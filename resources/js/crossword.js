// Reusable crossword engine. Works for any grid size: the server sends only
// geometry, clues and word lengths. Answers are checked server-side.

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
                el.addEventListener('click', () => selectCell(cell));
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
        if (!cursor.locked) cursor.letter = letter.toUpperCase();
        move(1);
        save();
        render();
    }

    function backspace() {
        if (!cursor || busy) return;
        clearWrongMarks();
        if (!cursor.letter || cursor.locked) move(-1);
        if (!cursor.locked) cursor.letter = '';
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
        else if (e.key === 'Enter') check();
        else return;
        e.preventDefault();
    });

    document.getElementById('cw-keyboard').addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-key]');
        if (!btn) return;
        if (!current) {
            const first = cells.values().next().value;
            if (first) selectCell(first);
        }
        btn.dataset.key === 'Backspace' ? backspace() : typeLetter(btn.dataset.key);
    });

    document.getElementById('cw-clear').addEventListener('click', clearWord);
    checkBtn.addEventListener('click', check);

    // ---- Validation (server side) ----------------------------------------
    async function check() {
        if (busy) return;
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
            for (const [id, status] of Object.entries(data.words)) {
                const word = wordById.get(Number(id));
                if (status === 'correct') word.cells.forEach((c) => (c.locked = true));
                if (status === 'wrong') {
                    wrong++;
                    word.cells.forEach((c) => !c.locked && c.el.classList.add('cw-cell--wrong'));
                }
            }
            render();
            // render() resets only active/word/correct classes, so wrong marks persist.

            if (data.solved) {
                messageEl.textContent = 'You solved it! Opening your gift…';
                clear();
                setTimeout(() => (window.location.href = data.redirect), 1200);
                return;
            }
            messageEl.textContent = wrong
                ? `${wrong} word${wrong > 1 ? 's are' : ' is'} not right yet. Keep trying!`
                : 'So far so good. Keep going!';
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
