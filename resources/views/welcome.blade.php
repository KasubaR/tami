@php
    $name = config('game.recipient');
    $birthday = \Illuminate\Support\Carbon::parse(config('game.birthday'));
    // Photos live in public/images/tamara/photo-1.jpeg ... photo-5.jpeg (kept out of git).
    $opensTarget = $opensAt->format('Y-m-d\TH:i:sP');
    // The countdown runs to midnight at the start of the birthday (Zambian time); the game itself opens later.
    $birthdayTarget = $birthday->copy()->shiftTimezone(\App\Support\GameSchedule::TIMEZONE)->startOfDay()->format('Y-m-d\TH:i:sP');
    $photos = collect(range(1, 5))
        ->filter(fn ($i) => file_exists(public_path("images/tamara/photo-$i.jpeg")))
        ->values();
    // Optional music: drop .mp3/.m4a/.ogg/.wav files into public/audio/ and they become the playlist.
    // With none, the player plays a built-in "Happy Birthday" tune.
    $tracks = collect(glob(public_path('audio/*.{mp3,m4a,ogg,wav}'), GLOB_BRACE) ?: [])
        ->sort()
        ->map(fn ($f) => [
            'src' => asset('audio/'.rawurlencode(basename($f))),
            'title' => \Illuminate\Support\Str::of(pathinfo($f, PATHINFO_FILENAME))->replace(['_', '-'], ' ')->trim()->toString(),
        ])
        ->values();
@endphp
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#F5F2ED">
<title>Happy Birthday, {{ $name }}!</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,500;1,600&family=Great+Vibes&display=swap" rel="stylesheet">
@verbatim
<style>
body{margin:0;background:#F5F2ED}
.w{width:100%;background:#F5F2ED;color:#2E2833;font-family:'Cormorant Garamond',Georgia,serif;display:flex;flex-direction:column;align-items:center;text-align:center;padding:0 0 70px;overflow:hidden}
.btn{display:inline-flex;align-items:center;justify-content:center;min-height:52px;padding:0 34px;box-sizing:border-box;border-radius:9999px;background:#8A4FA8;color:#fff;font-size:21px;font-weight:700;text-decoration:none;box-shadow:0 10px 24px -10px rgba(138,79,168,.6)}
.btn.locked{display:inline-block;background:#C3A3DA;box-shadow:none;cursor:not-allowed;font-size:19px;text-align:center;padding:10px 26px;line-height:1.25}
.btn:not(.locked):hover{background:#7E4A9E}.btn:focus-visible{outline:3px solid #C3A3DA;outline-offset:3px}
.hint{box-sizing:border-box;max-width:560px;margin:30px 20px 0;padding:22px 24px;border:1px solid #DCC6EC;border-radius:18px;background:rgba(255,255,255,.7)}
.music{width:200px;margin-top:26px;display:flex;flex-direction:column;align-items:center;gap:6px}
.music .bar{position:relative;width:100%;height:2px;background:#BFA5D2;cursor:pointer}
.music .bar i{position:absolute;left:0;top:0;height:2px;width:0;background:#8A4FA8}
.music .bar b{position:absolute;left:0;top:-4px;width:10px;height:10px;margin-left:-5px;border-radius:50%;background:#8A4FA8}
.music .ctl{display:flex;align-items:center;justify-content:center}
.music button{width:44px;height:44px;border:0;background:none;padding:0;color:#8A4FA8;cursor:pointer;border-radius:50%}
.music button.big{width:48px;height:48px}
.music button:focus-visible{outline:3px solid #C3A3DA}
.music button[aria-pressed=false]{opacity:.45}
.music button:disabled{opacity:.25;cursor:default}
.music .title{font-size:16px;font-style:italic;color:#6B5F73;min-height:22px}
.script{font-family:'Great Vibes',cursive;line-height:1.1}
.polaroid{flex:1 1 0;min-width:0;box-sizing:border-box;padding:5px 5px 16px;background:#fff;border-radius:4px;box-shadow:0 8px 20px rgba(46,40,51,.18);display:flex}
.polaroid:nth-child(1){transform:rotate(-3deg) translateY(6px)}
.polaroid:nth-child(2){transform:rotate(2deg) translateY(-6px)}
.polaroid:nth-child(3){transform:rotate(-1.5deg) translateY(8px)}
.polaroid:nth-child(4){transform:rotate(3deg) translateY(-4px)}
.polaroid:nth-child(5){transform:rotate(-2.5deg) translateY(6px)}
.photo{width:100%;aspect-ratio:3/4;background-color:#E6DDEE;background-size:cover;background-position:center 22%;background-repeat:no-repeat}
.dots{display:none}
@media (max-width:600px){
.row{display:block!important;position:relative;width:min(78vw,320px);max-width:320px!important;aspect-ratio:3/4.3;margin:0 auto!important}
.polaroid{position:absolute;inset:0;opacity:0;padding:6px 6px 22px!important;transform:rotate(-1.5deg)!important;animation:phone 30s infinite}
.polaroid:nth-child(even){transform:rotate(1.5deg)!important}
.polaroid:nth-child(2){animation-delay:6s}.polaroid:nth-child(3){animation-delay:12s}.polaroid:nth-child(4){animation-delay:18s}.polaroid:nth-child(5){animation-delay:24s}
.photo{aspect-ratio:auto!important;flex:1;height:auto}
.dots{display:flex;justify-content:center;gap:8px;margin-top:18px}
.dots span{width:9px;height:9px;border-radius:50%;background:#8A4FA8;opacity:.3;animation:dot 30s infinite}
.dots span:nth-child(2){animation-delay:6s}.dots span:nth-child(3){animation-delay:12s}.dots span:nth-child(4){animation-delay:18s}.dots span:nth-child(5){animation-delay:24s}
}
@keyframes phone{0%{opacity:0}3%{opacity:1}20%{opacity:1}26%{opacity:0}100%{opacity:0}}
@keyframes dot{0%,20%{opacity:1;transform:scale(1.3)}24%,100%{opacity:.3;transform:scale(1)}}
@media (max-width:600px) and (prefers-reduced-motion:reduce){.polaroid,.dots span{animation:none}.polaroid:first-child{opacity:1}}
</style>
@endverbatim
</head>
<body>
<div class="w">

@if ($photos->isNotEmpty())
    <div style="width:100%;box-sizing:border-box;padding:36px 16px 28px">
        <div class="row" style="display:flex;flex-wrap:nowrap;gap:10px;max-width:1000px;margin:0 auto">
            @foreach ($photos as $n => $i)
                <div class="polaroid"><div class="photo" role="img" aria-label="{{ $name }}, photo {{ $n + 1 }} of {{ $photos->count() }}"
                     style="background-image:url('{{ asset("images/tamara/photo-$i.jpeg") }}')"></div></div>
            @endforeach
        </div>
        <div class="dots" aria-hidden="true">@foreach ($photos as $_)<span></span>@endforeach</div>
    </div>
@endif

<div style="margin-top:8px;font-size:17px;font-weight:700;letter-spacing:3px">COUNTING DOWN TO YOUR BIRTHDAY</div>
<div id="countdown" data-target="{{ $birthdayTarget }}" data-opens="{{ $opensTarget }}" data-open="{{ $isOpen ? 1 : 0 }}" style="margin-top:14px;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:4px;width:100%;max-width:360px;padding:0 16px;box-sizing:border-box">
    @foreach (['days' => 'Days', 'hours' => 'Hours', 'minutes' => 'Min', 'seconds' => 'Sec'] as $key => $label)
        <div style="display:flex;flex-direction:column;gap:4px"><span data-unit="{{ $key }}" style="font-size:50px;line-height:1;color:#8A4FA8">0</span><span style="font-size:16px;font-weight:600">{{ $label }}</span></div>
    @endforeach
</div>

<div style="margin-top:44px;font-size:18px;font-weight:700;letter-spacing:6px;color:#7E4A9E">HAPPY BIRTHDAY</div>
<h1 class="script" style="margin:8px 20px 0;font-size:clamp(46px,13vw,76px);font-weight:400">{{ config('game.full_name') }}</h1>
<div class="music" id="music" role="group" aria-label="Music player">
    <div class="bar" id="music-bar" aria-hidden="true"><i id="music-fill"></i><b id="music-dot"></b></div>
    <div class="ctl">
        <button type="button" id="m-shuffle" aria-label="Shuffle" aria-pressed="false"><svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h4l10 10h4M3 17h4l3-3M14 10l3-3h4M18 4l3 3-3 3M18 14l3 3-3 3"/></svg></button>
        <button type="button" id="m-prev" aria-label="Previous"><svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M6 5h2v14H6zM20 5v14L9 12z"/></svg></button>
        <button type="button" id="m-play" class="big" aria-label="Play"><svg aria-hidden="true" width="40" height="40" viewBox="0 0 40 40"><circle cx="20" cy="20" r="20" fill="currentColor"/><path id="m-play-shape" d="M16 12l13 8-13 8z" fill="#FFFFFF"/></svg></button>
        <button type="button" id="m-next" aria-label="Next"><svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M16 5h2v14h-2zM4 5v14l11-7z"/></svg></button>
        <button type="button" id="m-repeat" aria-label="Repeat" aria-pressed="false"><svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11V9a3 3 0 0 1 3-3h13M17 3l3 3-3 3M20 13v2a3 3 0 0 1-3 3H4M7 21l-3-3 3-3"/></svg></button>
    </div>
    <div class="title" id="music-title" aria-live="polite">{{ $tracks->isNotEmpty() ? $tracks->first()['title'] : 'Happy Birthday' }}</div>
</div>

<p style="margin:22px 28px 0;max-width:560px;font-size:23px;line-height:1.5;font-weight:600">On this special day, I wish you a year filled with joy, laughter, love and every happiness your heart can hold.</p>

<p style="margin:26px 28px 0;max-width:520px;font-size:20px;line-height:1.5;font-weight:600;color:#6B5F73">{{ config('game.how_it_works') }}</p>
@if ($isOpen)
    <a href="{{ url('/play') }}" class="btn" style="margin-top:22px">{{ $started ? 'Continue My Adventure' : 'Start My Adventure' }}</a>
@endif
<p style="margin:16px 28px 0;font-size:18px;font-style:italic;color:#6B5F73">Eleven puzzles. Eleven surprises. One unforgettable birthday.</p>
<div class="hint">
    <div style="font-size:16px;font-weight:700;letter-spacing:3px;color:#7E4A9E">A LITTLE HINT</div>
    <p style="margin:10px 0 0;font-size:20px;line-height:1.5;font-weight:600">Eleven crosswords are waiting for you. In every puzzle, one of the answers is a surprise just for you. Solve them one at a time, in order, to unwrap all eleven, and when the last one is done there is one more surprise at the very end.</p>
    <p style="margin:10px 0 0;font-size:18px;line-height:1.5;color:#6B5F73">Tap a square, read the clue, and use the keyboard. It only shows the letters each puzzle uses.</p>
</div>

<div style="margin-top:48px;font-size:16px;font-weight:700;letter-spacing:3px">{{ strtoupper($birthday->format('F')) }}</div>
<div style="margin-top:6px;display:flex;align-items:center;justify-content:center;gap:14px">
    <span style="width:110px;padding:9px 0;border-top:1px solid #2E2833;border-bottom:1px solid #2E2833;font-size:15px;font-weight:700;letter-spacing:2px">{{ strtoupper($birthday->format('l')) }}</span>
    <span style="font-size:62px;line-height:1;font-style:italic;font-weight:500;color:#8A4FA8">{{ $birthday->format('j') }}</span>
    <span style="width:110px;padding:9px 0;border-top:1px solid #2E2833;border-bottom:1px solid #2E2833;font-size:15px;font-weight:700;letter-spacing:2px">{{ $birthday->format('Y') }}</span>
</div>



<svg aria-hidden="true" width="40" height="40" viewBox="0 0 40 40" fill="none" stroke="#8A4FA8" stroke-width="1.8" stroke-linejoin="round" style="margin-top:56px"><path d="M6 14h28v8H6zM9 22h22v13H9zM20 14v21M20 14c-3-8-11-6-9-2 1 2 5 2 9 2zM20 14c3-8 11-6 9-2-1 2-5 2-9 2z"/></svg>
<h2 style="margin:16px 0 0;font-size:18px;font-weight:700;letter-spacing:3px">A WISH FOR YOU</h2>
<p style="margin:12px 28px 0;max-width:560px;font-size:21px;line-height:1.5;font-weight:600">May every dream you carry come true, may every step you take be full of light, and may you always know how deeply you are loved.</p>

<svg aria-hidden="true" width="40" height="40" viewBox="0 0 40 40" fill="none" stroke="#8A4FA8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-top:64px"><path d="M5 8h30v27H5zM5 15h30M12 4v8M28 4v8M20 30c-8-5-7-11-3-11 2 0 3 2 3 2s1-2 3-2c4 0 5 6-3 11z"/></svg>
<h2 style="margin:16px 0 0;font-size:18px;font-weight:700;letter-spacing:3px">MAKE A WISH</h2>
<p style="margin:12px 28px 0;max-width:520px;font-size:21px;line-height:1.5;font-weight:600">Close your eyes, make a wish, and blow out the candles. This year is yours.</p>

<p style="margin:36px 28px 0;max-width:560px;font-size:15px;line-height:1.7;font-weight:700;letter-spacing:1px">THANK YOU FOR BEING YOU. YOUR SMILE MAKES EVERY DAY BRIGHTER.</p>
<p style="margin:24px 28px 0;max-width:560px;font-size:15px;line-height:1.7;font-weight:700;letter-spacing:1px">WITH ALL MY LOVE, ENJOY EVERY MOMENT OF YOUR BIRTHDAY.</p>
<div class="script" style="margin-top:44px;font-size:60px">Happy Birthday!</div>
@if ($isOpen)
    <a href="{{ url('/play') }}" class="btn" style="margin-top:32px">{{ $started ? 'Continue My Adventure' : 'Start My Adventure' }}</a>
@endif
</div>

<script>
(function () {
  var box = document.getElementById('countdown');
  if (!box) return;
  var target = new Date(box.dataset.target).getTime();
  var opens = new Date(box.dataset.opens).getTime();
  var els = {};
  box.querySelectorAll('[data-unit]').forEach(function (e) { els[e.dataset.unit] = e; });
  function pad(n) { return n < 10 ? '0' + n : String(n); }
  function tick() {
    var left = Math.max(0, Math.floor((target - Date.now()) / 1000));
    if (isNaN(left)) left = 0;
    els.days.textContent = Math.floor(left / 86400);
    els.hours.textContent = pad(Math.floor(left % 86400 / 3600));
    els.minutes.textContent = pad(Math.floor(left % 3600 / 60));
    els.seconds.textContent = pad(left % 60);
    if (box.dataset.open === '0' && Date.now() >= opens) location.reload();
  }
  tick();
  setInterval(tick, 1000);
})();
</script>

<script>
(function () {
  var tracks = @json($tracks);
  var el = function (id) { return document.getElementById(id); };
  var playBtn = el('m-play'), shape = el('m-play-shape'), fill = el('music-fill'), dot = el('music-dot'), title = el('music-title');
  var shuffleBtn = el('m-shuffle'), repeatBtn = el('m-repeat'), prevBtn = el('m-prev'), nextBtn = el('m-next');
  var shuffle = false, repeat = false, playing = false;
  var PLAY = 'M16 12l13 8-13 8z', PAUSE = 'M14 12h4v16h-4zM22 12h4v16h-4z';

  function paint(frac) {
    frac = Math.max(0, Math.min(1, frac || 0));
    fill.style.width = (frac * 100) + '%';
    dot.style.left = (frac * 100) + '%';
  }
  function setPlaying(on) {
    playing = on;
    shape.setAttribute('d', on ? PAUSE : PLAY);
    playBtn.setAttribute('aria-label', on ? 'Pause' : 'Play');
  }
  function flag(btn, on) { btn.setAttribute('aria-pressed', on ? 'true' : 'false'); }
  shuffleBtn.addEventListener('click', function () { shuffle = !shuffle; flag(shuffleBtn, shuffle); });
  repeatBtn.addEventListener('click', function () { repeat = !repeat; flag(repeatBtn, repeat); });

  if (tracks.length) {
    // Playlist from public/audio
    var audio = new Audio();
    audio.preload = 'none';
    var i = 0;
    var load = function (n) { i = (n + tracks.length) % tracks.length; audio.src = tracks[i].src; title.textContent = tracks[i].title; paint(0); };
    var go = function (step) {
      var n = shuffle && tracks.length > 1 ? i + 1 + Math.floor(Math.random() * (tracks.length - 1)) : i + step;
      load(n);
      if (playing) audio.play().catch(function () { setPlaying(false); });
    };
    load(0);
    playBtn.addEventListener('click', function () {
      if (playing) { audio.pause(); setPlaying(false); }
      else { audio.play().then(function () { setPlaying(true); }).catch(function () { setPlaying(false); }); }
    });
    nextBtn.addEventListener('click', function () { go(1); });
    prevBtn.addEventListener('click', function () { go(-1); });
    audio.addEventListener('timeupdate', function () { paint(audio.duration ? audio.currentTime / audio.duration : 0); });
    audio.addEventListener('ended', function () {
      var last = !shuffle && i === tracks.length - 1;
      if (tracks.length === 1 || last) {
        if (repeat) { if (tracks.length === 1) { audio.currentTime = 0; audio.play(); } else { go(1); } }
        else { setPlaying(false); paint(0); }
        return;
      }
      go(1);
    });
    el('music-bar').addEventListener('click', function (e) {
      if (!audio.duration) return;
      var r = this.getBoundingClientRect();
      audio.currentTime = audio.duration * Math.max(0, Math.min(1, (e.clientX - r.left) / r.width));
    });
    return;
  }

  // No audio files: play a built-in "Happy Birthday" tune.
  prevBtn.disabled = true; nextBtn.disabled = true; shuffleBtn.disabled = true;
  var ctx = null, started = false, total = 0, timer = null, beat = 0.42;
  var C2 = 523, B = 494, A = 440, G = 392, F = 349, E = 330, D = 294;
  var tune = [[G,.75],[G,.25],[A,1],[G,1],[C2,1],[B,2],[G,.75],[G,.25],[A,1],[G,1],[D*2,1],[C2,2],
              [G,.75],[G,.25],[G*2,1],[E*2,1],[C2,1],[B,1],[A,1],[F*2,.75],[F*2,.25],[E*2,1],[C2,1],[D*2,1],[C2,2]];
  tune.forEach(function (n) { total += n[1] * beat; });

  function stop() {
    clearInterval(timer);
    if (ctx) { ctx.close(); ctx = null; }
    started = false; setPlaying(false); paint(0);
  }
  function schedule() {
    var AC = window.AudioContext || window.webkitAudioContext;
    ctx = new AC();
    var t = 0.05;
    tune.forEach(function (n) {
      var o = ctx.createOscillator(), g = ctx.createGain(), len = n[1] * beat;
      o.type = 'triangle'; o.frequency.value = n[0];
      g.gain.setValueAtTime(0, ctx.currentTime + t);
      g.gain.linearRampToValueAtTime(0.16, ctx.currentTime + t + 0.02);
      g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + t + len * 0.95);
      o.connect(g); g.connect(ctx.destination);
      o.start(ctx.currentTime + t); o.stop(ctx.currentTime + t + len);
      t += len;
    });
    started = true;
    clearInterval(timer);
    timer = setInterval(function () {
      if (!ctx) return;
      var f = ctx.currentTime / (total + 0.2);
      paint(f);
      if (f >= 1) { var again = repeat; stop(); if (again) { schedule(); setPlaying(true); } }
    }, 100);
  }
  playBtn.addEventListener('click', function () {
    try {
      if (!started) { schedule(); setPlaying(true); }
      else if (playing) { ctx.suspend(); setPlaying(false); }
      else { ctx.resume(); setPlaying(true); }
    } catch (e) { setPlaying(false); }
  });
})();
</script>
</body>
</html>
