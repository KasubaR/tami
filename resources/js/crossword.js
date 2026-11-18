// Reusable crossword engine. Works for any grid size: the server sends only
// geometry, clues and word lengths. Answers are checked server-side.

import { bindSoundToggle, playBack, playCorrect, playReveal, playTick, playWrong } from './sound';

const root = document.getElementById('crossword');

if (root) {
    const puzzle = JSON.parse(document.getElementById('puzzle-data').textContent);
    const checkUrl = root.dataset.checkUrl;
    const storageKey = root.dataset.storageKey;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    const gridEl = document.getElementById('cw-grid');
    const clueLabel = document.getElementById('cw-clue-label');
    const clueText = document.getElementById('cw-clue-text');
    const messageEl = document.getElementById('cw-message');
    const checkBtn = document.getElementById('cw-check');

    const key = (r, c) => `${r},${c}`;

    // ---- Model -----------------------------------------------------------
    const cells = new Map(); // "r,c" -> { r, c, el, letterEl, number, words: {across?, down?}, letter, locked }
    const words = puzzle.words.map((w) => ({ ...w, cells: [] }));
    const wordById = new Map(words.map((w) => [w.id, w]));

    for (const word of words) {
        for (let i = 0; i < word.length; i++) {
            const r = word.direction === 'down' ? word.row + i : word.row;
            const c = word.direction === 'across' ? word.col + i : word.col;
            let cell = cells.get(key(r, c));
            if (!cell) {
                cell = { r, c, words: {}, letter: '', locked: false };
                cells.set(key(r, c), cell);
            }
            cell.words[word.direction] = word; // intersections have both directions
            word.cells.push(cell);
        }
        const start = cells.get(key(word.row, word.col));
        start.number = word.number;
    }

    // ---- State -----------------------------------------------------------
    let current = null; // selected word
    let cursor = null; // selected cell
    let busy = false;

    // ---- Build DOM -------------------------------------------------------
    gridEl.style.gridTemplateColumns = `repeat(${puzzle.cols}, minmax(0, 1fr))`;
    gridEl.style.maxWidth = `${Math.min(puzzle.cols * 64, 520)}px`;

    for (let r = 0; r < puzzle.rows; r++) {
        for (let c = 0; c < puzzle.cols; c++) {
            const cell = cells.get(key(r, c));
            const el = document.createElement('div');
            if (!cell) {
                el.className = 'cw-cell cw-cell--block'; // blocked
                el.setAttribute('aria-hidden', 'true');
            } else {
                el.className = 'cw-cell grid cursor-pointer place-items-center';
                el.setAttribute('role', 'gridcell');
                el.dataset.cell = key(r, c);
                if (cell.number) {
                    const n = document.createElement('span');
                    n.className = 'absolute left-0.5 top-0 text-[10px] font-semibold leading-tight text-ink-soft';
                    n.textContent = cell.number;
                    el.appendChild(n);
                }
                const letterEl = document.createElement('span');
                letterEl.className = 'text-xl sm:text-2xl';
                el.appendChild(letterEl);
                cell.el = el;
                cell.letterEl = letterEl;
                el.addEventListener('click', () => !justDragged && selectCell(cell));
            }
            gridEl.appendChild(el);
        }
    }

    const clueItems = new Map();
    for (const word of words) {
        const li = document.createElement('li');
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'w-full rounded-lg px-2 py-1 text-left text-sm hover:bg-champagne-50';
        btn.innerHTML = '<b class="mr-1 text-champagne-600"></b><span></span> <span class="text-ink-soft"></span>';
        btn.children[0].textContent = `${word.number}.`;
        btn.children[1].textContent = word.clue;
        btn.children[2].textContent = `(${word.length})`;
        btn.addEventListener('click', () => selectWord(word, word.cells.find((c) => !c.letter) ?? word.cells[0]));
        li.appendChild(btn);
        document.getElementById(`cw-clues-${word.direction}`).appendChild(li);
        clueItems.set(word, btn);
    }

    // ---- Selection -------------------------------------------------------
    function selectCell(cell) {
        const { across, down } = cell.words;
        let word;
        if (cursor === cell && across && down) {
            word = current === across ? down : across; // tap again toggles direction
        } else if (current && cell.words[current.direction]) {
            word = cell.words[current.direction]; // keep the direction when possible
        } else {
            word = across ?? down;
        }
        selectWord(word, cell);
    }

    function selectWord(word, cell) {
        current = word;
        cursor = cell;
        render();
    }

    function render() {
        for (const cell of cells.values()) {
            cell.el.classList.toggle('cw-cell--word', !!current && current.cells.includes(cell));
            cell.el.classList.toggle('cw-cell--active', cell === cursor);
            cell.el.classList.toggle('cw-cell--correct', cell.locked);
            cell.letterEl.textContent = cell.letter;
            cell.el.style.touchAction = cell.letter && !cell.locked ? 'none' : ''; // so a touch-drag moves the letter, not the page
        }
        for (const [word, btn] of clueItems) {
            btn.classList.toggle('bg-champagne-100', word === current);
        }
        if (current) {
            clueLabel.textContent = `${current.number} ${current.direction === 'across' ? 'Across' : 'Down'}`;
            clueText.textContent = `${current.clue} (${current.length})`;
        }
    }

    // ---- Input -----------------------------------------------------------
    function clearWrongMarks() {
        for (const cell of cells.values()) cell.el.classList.remove('cw-cell--wrong');
        messageEl.textContent = '';
    }

    function move(offset) {
        if (!current || !cursor) return;
        const next = current.cells[current.cells.indexOf(cursor) + offset];
        if (next) cursor = next;
    }

    // The keyboard only offers this level's letters; the physical keyboard follows the same rule.
    const allowed = new Set(puzzle.letters ?? []);

    function typeLetter(letter) {
        if (!cursor || busy) return;
        if (allowed.size && !allowed.has(letter.toUpperCase())) return;
        clearWrongMarks();
        const typed = cursor;
        if (!cursor.locked) cursor.letter = letter.toUpperCase();
        playTick();
        move(1);
        save();
        render();
        // Finishing a word (this one or a crossing one) checks it straight away.
        const finished = Object.values(typed.words).filter((w) => w.cells.every((c) => c.letter) && !w.cells.every((c) => c.locked));
        if (finished.length) check(finished.includes(current) ? current : finished[0]);
    }

    function backspace() {
        if (!cursor || busy) return;
        clearWrongMarks();
        if (!cursor.letter || cursor.locked) move(-1);
        if (!cursor.locked) cursor.letter = '';
        playBack();
        save();
        render();
    }

    function clearWord() {
        if (!current || busy) return;
        clearWrongMarks();
        for (const cell of current.cells) if (!cell.locked) cell.letter = '';
        cursor = current.cells[0];
        save();
        render();
    }

    function arrow(dr, dc) {
        if (!cursor) return;
        const next = cells.get(key(cursor.r + dr, cursor.c + dc));
        if (!next) return;
        // Moving along the axis of the current word keeps it selected.
        const direction = dr ? 'down' : 'across';
        const word = next.words[direction] ?? next.words[current?.direction] ?? next.words.across ?? next.words.down;
        selectWord(word, next);
    }

    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey || e.metaKey || e.altKey || busy) return;
        if (!current) {
            const first = cells.values().next().value;
            if (/^(\p{L}|Backspace|Arrow.*)$/u.test(e.key) && first) selectCell(first);
            else return;
        }
        if (e.key.length === 1 && /\p{L}/u.test(e.key)) typeLetter(e.key);
        else if (e.key === 'Backspace') backspace();
        else if (e.key === 'Delete') clearWord();
        else if (e.key === 'ArrowLeft') arrow(0, -1);
        else if (e.key === 'ArrowRight') arrow(0, 1);
        else if (e.key === 'ArrowUp') arrow(-1, 0);
        else if (e.key === 'ArrowDown') arrow(1, 0);
        else if (e.key === ' ' && cursor) selectCell(cursor);
        else if (e.key === 'Enter') check();        else return;
        e.preventDefault();
    });

    // ---- Drag a letter from the keyboard onto a square --------------------
    const keyboardEl = document.getElementById('cw-keyboard');
    const DROP_RING = ['ring-2', 'ring-inset', 'ring-champagne-500'];
    let drag = null; // { btn, startX, startY, ghost, over }
    let justDragged = false;

    const cellAt = (x, y) => {
        const el = document.elementFromPoint(x, y)?.closest('[data-cell]');
        return el ? cells.get(el.dataset.cell) : null;
    };

    function endDrag() {
        drag?.ghost?.remove();
        drag?.source?.letterEl.classList.remove('opacity-30');
        drag?.over?.el.classList.remove(...DROP_RING);
        drag = null;
    }

    keyboardEl.addEventListener('pointerdown', (e) => {
        const btn = e.target.closest('button[data-key]');
        if (!btn || btn.dataset.key === 'Backspace' || busy || e.button > 0) return;
        drag = { letter: btn.dataset.key, source: null, startX: e.clientX, startY: e.clientY, ghost: null, over: null };
    });

    // A letter already in the grid can be picked up and moved to the right square.
    gridEl.addEventListener('pointerdown', (e) => {
        const cell = cells.get(e.target.closest('[data-cell]')?.dataset.cell);
        if (!cell || !cell.letter || cell.locked || busy || e.button > 0) return;
        drag = { letter: cell.letter, source: cell, startX: e.clientX, startY: e.clientY, ghost: null, over: null };
    });

    document.addEventListener('pointermove', (e) => {
        if (!drag) return;
        if (!drag.ghost) {
            if (Math.hypot(e.clientX - drag.startX, e.clientY - drag.startY) < 8) return;
            const ghost = document.createElement('div');
            ghost.textContent = drag.letter;
            ghost.className = 'pointer-events-none fixed z-50 grid h-14 w-14 place-items-center rounded-xl border-2 border-champagne-500 bg-white text-2xl font-bold shadow-lg';
            document.body.appendChild(ghost);
            drag.ghost = ghost;
            drag.source?.letterEl.classList.add('opacity-30');
        }
        drag.ghost.style.left = `${e.clientX - 28}px`;
        drag.ghost.style.top = `${e.clientY - 64}px`; // sits above the finger
        const over = cellAt(e.clientX, e.clientY);
        if (over !== drag.over) {
            drag.over?.el.classList.remove(...DROP_RING);
            over?.el.classList.add(...DROP_RING);
            drag.over = over;
        }
        e.preventDefault();
    });

    document.addEventListener('pointerup', (e) => {
        if (!drag) return;
        if (drag.ghost) {
            justDragged = true; // swallow the click that follows a drag
            setTimeout(() => (justDragged = false), 0);
            const target = cellAt(e.clientX, e.clientY);
            const { letter, source } = drag;
            endDrag();
            if (target && !busy && target !== source && !target.locked) {
                if (source) source.letter = target.letter; // moving a letter: swap with what was there
                if (current && target.words[current.direction] === current) {
                    cursor = target;
                } else {
                    current = target.words[current?.direction] ?? target.words.across ?? target.words.down;
                    cursor = target;
                }
                typeLetter(letter);
            }
        } else {
            endDrag();
        }
    });

    document.addEventListener('pointercancel', endDrag);

    keyboardEl.addEventListener('click', (e) => {
        if (justDragged) return;
        const btn = e.target.closest('button[data-key]');
        if (!btn) return;
        if (!current) {
            const first = cells.values().next().value;
            if (first) selectCell(first);
        }
        btn.dataset.key === 'Backspace' ? backspace() : typeLetter(btn.dataset.key);
    });

    document.getElementById('cw-clear').addEventListener('click', clearWord);
    checkBtn.addEventListener('click', () => check());
    bindSoundToggle(document.querySelector('[data-sound-toggle]'));

    // ---- Validation (server side) ----------------------------------------
    // Bounce the word's squares one after another.
    function celebrateWord(word) {
        word.cells.forEach((cell, i) => {
            const el = cell.el;
            el.classList.remove('cw-cell--win');
            void el.offsetWidth; // restart the animation if it is still running
            el.style.animationDelay = `${i * 70}ms`;
            el.classList.add('cw-cell--win');
            setTimeout(() => {
                el.classList.remove('cw-cell--win');
                el.style.animationDelay = '';
            }, 700 + i * 70);
        });
    }

    // `auto` is the word that was just completed (automatic check); the buttons call it with no argument.
    async function check(auto) {
        if (busy) return;
        auto = auto?.cells ? auto : null;
        const payload = {};
        for (const cell of cells.values()) if (cell.letter) payload[key(cell.r, cell.c)] = cell.letter;
        if (!Object.keys(payload).length) {
            messageEl.textContent = 'Fill in some letters first.';
            return;
        }

        busy = true;
        checkBtn.disabled = true;
        clearWrongMarks();
        try {
            const res = await fetch(checkUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ cells: payload }),
            });
            if (!res.ok) throw new Error(res.status);
            const data = await res.json();

            let wrong = 0;
            let newlyCorrect = 0;
            for (const [id, status] of Object.entries(data.words)) {
                const word = wordById.get(Number(id));
                if (status === 'correct') {
                    if (!word.cells.every((c) => c.locked)) {
                        newlyCorrect++;
                        celebrateWord(word);
                    }
                    word.cells.forEach((c) => (c.locked = true));
                }
                if (status === 'wrong') {
                    wrong++;
                    word.cells.forEach((c) => !c.locked && c.el.classList.add('cw-cell--wrong'));
                }
            }
            render();
            // render() resets only active/word/correct classes, so wrong marks persist.

            if (data.solved) {
                playReveal();
                messageEl.textContent = 'You solved it! Opening your gift…';
                clear();
                setTimeout(() => (window.location.href = data.redirect), 1200);
                return;
            }
            if (wrong) playWrong();
            else if (newlyCorrect) playCorrect();

            // Reject wrong words: show them red for a moment, then clear their letters.
            if (wrong) {
                messageEl.textContent = `${wrong} word${wrong > 1 ? 's are' : ' is'} not right. Try again!`;
                await new Promise((resolve) => setTimeout(resolve, 900));
                clearWrongMarks();
                const rejected = Object.entries(data.words)
                    .filter(([, status]) => status === 'wrong')
                    .map(([id]) => wordById.get(Number(id)));
                for (const word of rejected) for (const cell of word.cells) if (!cell.locked) cell.letter = '';
                const focus = auto && rejected.includes(auto) ? auto : rejected.find((w) => w.cells.includes(cursor)) ?? rejected[0];
                if (focus) {
                    current = focus;
                    cursor = focus.cells.find((c) => !c.locked) ?? focus.cells[0];
                }
                save();
                render();
                return;
            }
            messageEl.textContent = 'So far so good. Keep going!';
        } catch (err) {
            messageEl.textContent = 'Could not check your answers. Please try again.';
        } finally {
            busy = false;
            checkBtn.disabled = false;
        }
    }

    // ---- Persistence (per-viewer convenience only) -----------------------
    function save() {
        try {
            const letters = {};
            for (const cell of cells.values()) if (cell.letter) letters[key(cell.r, cell.c)] = cell.letter;
            localStorage.setItem(storageKey, JSON.stringify(letters));
        } catch {}
    }

    function clear() {
        try {
            localStorage.removeItem(storageKey);
        } catch {}
    }

    try {
        const saved = JSON.parse(localStorage.getItem(storageKey) ?? '{}');
        for (const [k, letter] of Object.entries(saved)) {
            const cell = cells.get(k);
            if (cell && typeof letter === 'string') cell.letter = letter.slice(0, 1).toUpperCase();
        }
    } catch {}

    // Words already solved (saved on the server) come back filled in and locked.
    for (const [id, answer] of Object.entries(puzzle.solved ?? {})) {
        const word = wordById.get(Number(id));
        if (!word) continue;
        word.cells.forEach((cell, i) => {
            cell.letter = answer[i] ?? cell.letter;
            cell.locked = true;
        });
    }

    // Start on the first word so a clue is visible straight away.
    if (words.length) selectWord(words[0], words[0].cells[0]);
    else render();
}
