@php
    $name = config('game.recipient');
    $birthday = \Illuminate\Support\Carbon::parse(config('game.birthday'));
    // Photos live in public/images/tamara/photo-1.jpeg ... photo-5.jpeg (kept out of git).
    $photos = collect(range(1, 5))
        ->filter(fn ($i) => file_exists(public_path("images/tamara/photo-$i.jpeg")))
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
.btn:hover{background:#7E4A9E}.btn:focus-visible{outline:3px solid #C3A3DA;outline-offset:3px}
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

<div style="margin-top:32px;font-size:18px;font-weight:700;letter-spacing:6px;color:#7E4A9E">HAPPY BIRTHDAY</div>
<h1 class="script" style="margin:8px 0 0;font-size:76px;font-weight:400">{{ $name }}</h1>
<p style="margin:22px 28px 0;max-width:560px;font-size:23px;line-height:1.5;font-weight:600">On this special day, I wish you a year filled with joy, laughter, love and every happiness your heart can hold.</p>

<p style="margin:26px 28px 0;max-width:520px;font-size:20px;line-height:1.5;font-weight:600;color:#6B5F73">{{ config('game.how_it_works') }}</p>
<a href="{{ url('/play') }}" class="btn" style="margin-top:22px">{{ $started ? 'Continue My Adventure' : 'Start My Adventure' }}</a>
<p style="margin:16px 28px 0;font-size:18px;font-style:italic;color:#6B5F73">Eleven puzzles. Eleven surprises. One unforgettable birthday.</p>

<div style="margin-top:48px;font-size:16px;font-weight:700;letter-spacing:3px">{{ strtoupper($birthday->format('F')) }}</div>
<div style="margin-top:6px;display:flex;align-items:center;justify-content:center;gap:14px">
    <span style="width:110px;padding:9px 0;border-top:1px solid #2E2833;border-bottom:1px solid #2E2833;font-size:15px;font-weight:700;letter-spacing:2px">{{ strtoupper($birthday->format('l')) }}</span>
    <span style="font-size:62px;line-height:1;font-style:italic;font-weight:500;color:#8A4FA8">{{ $birthday->format('j') }}</span>
    <span style="width:110px;padding:9px 0;border-top:1px solid #2E2833;border-bottom:1px solid #2E2833;font-size:15px;font-weight:700;letter-spacing:2px">{{ $birthday->format('Y') }}</span>
</div>

<div style="margin-top:48px;font-size:17px;font-weight:700;letter-spacing:3px">COUNTING DOWN TO YOUR DAY</div>
<div id="countdown" data-target="{{ $birthday->format('Y-m-d') }}T00:00:00" style="margin-top:14px;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:4px;width:100%;max-width:360px;padding:0 16px;box-sizing:border-box">
    @foreach (['days' => 'Days', 'hours' => 'Hours', 'minutes' => 'Min', 'seconds' => 'Sec'] as $key => $label)
        <div style="display:flex;flex-direction:column;gap:4px"><span data-unit="{{ $key }}" style="font-size:50px;line-height:1;color:#8A4FA8">0</span><span style="font-size:16px;font-weight:600">{{ $label }}</span></div>
    @endforeach
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
<a href="{{ url('/play') }}" class="btn" style="margin-top:32px">{{ $started ? 'Continue My Adventure' : 'Start My Adventure' }}</a>
</div>

<script>
(function () {
  var box = document.getElementById('countdown');
  if (!box) return;
  var target = new Date(box.dataset.target).getTime();
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
  }
  tick();
  setInterval(tick, 1000);
})();
</script>
</body>
</html>
