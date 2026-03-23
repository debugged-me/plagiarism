<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PlagiaScope — Checker</title>
  <script src="config.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *,
    *::before,
    *::after {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --bg: #f5f3ef;
      --surface: #ffffff;
      --s2: #eeecea;
      --border: #ddd9d0;
      --border2: #ccc8be;
      --ink: #0e0c09;
      --ink2: #2a2620;
      --muted: #6b6560;
      --faint: #a09890;
      --accent: #1a3de4;
      --acc2: #4466f5;
      --acc-soft: #eaedff;
      --acc-brd: #b8c4fd;
      --danger: #d42020;
      --dbg: #fef2f2;
      --dbrd: #fecaca;
      --warn: #c47a00;
      --wbg: #fffbeb;
      --wbrd: #fde68a;
      --ok: #157a3a;
      --okbg: #ecfdf3;
      --okbrd: #a7f3c8;
      --nav-bg: rgba(245, 243, 239, .94);
      --sh: 0 1px 6px rgba(14, 12, 9, .07);
      --sh2: 0 4px 18px rgba(14, 12, 9, .09);
      --r: 14px;
      --rs: 9px;
      --rx: 7px;
    }

    [data-theme="dark"] {
      --bg: #080a0f;
      --surface: #0f1119;
      --s2: #161b26;
      --border: #1f2840;
      --border2: #263350;
      --ink: #eceef8;
      --ink2: #bfc6dc;
      --muted: #6e7d9a;
      --faint: #3a4560;
      --accent: #5577ff;
      --acc2: #7a99ff;
      --acc-soft: #0e1530;
      --acc-brd: #1e3060;
      --danger: #f87171;
      --dbg: #200a0a;
      --dbrd: #450a0a;
      --warn: #fbbf24;
      --wbg: #1a1000;
      --wbrd: #3a2500;
      --ok: #34d375;
      --okbg: #042010;
      --okbrd: #0d4020;
      --nav-bg: rgba(8, 10, 15, .94);
      --sh: 0 1px 6px rgba(0, 0, 0, .35);
      --sh2: 0 4px 18px rgba(0, 0, 0, .45);
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      background: var(--bg);
      color: var(--ink);
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      -webkit-font-smoothing: antialiased;
      transition: background .4s, color .4s;
    }

    /* ── SCROLL BAR ── */
    #spb {
      position: fixed;
      top: 0;
      left: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--accent), var(--acc2));
      z-index: 9998;
      width: 0;
      transition: width .08s linear;
      box-shadow: 0 0 10px rgba(26, 61, 228, .5);
    }

    /* ── TOPBAR ── */
    .topbar {
      background: var(--nav-bg);
      border-bottom: 1px solid var(--border);
      padding: 0 36px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 50;
      backdrop-filter: blur(20px) saturate(180%);
      box-shadow: var(--sh);
      animation: topIn .5s ease both;
      transition: background .4s;
    }

    @keyframes topIn {
      from {
        opacity: 0;
        transform: translateY(-100%);
      }

      to {
        opacity: 1;
        transform: none;
      }
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
    }

    .brand-ico {
      width: 32px;
      height: 32px;
      background: var(--accent);
      border-radius: 10px;
      display: grid;
      place-items: center;
      font-size: 15px;
      transition: transform .35s cubic-bezier(.34, 1.56, .64, 1), background .2s;
    }

    .brand:hover .brand-ico {
      transform: rotate(-15deg) scale(1.1);
      background: var(--ink);
    }

    .brand-name {
      font-family: 'Playfair Display', serif;
      font-size: 19px;
      letter-spacing: -.02em;
      color: var(--ink);
    }

    .brand-name em {
      font-style: italic;
      color: var(--accent);
    }

    .top-r {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .dm-btn {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      border: 1.5px solid var(--border);
      background: var(--surface);
      display: grid;
      place-items: center;
      cursor: pointer;
      font-size: 16px;
      transition: all .2s;
    }

    .dm-btn:hover {
      border-color: var(--acc-brd);
      background: var(--acc-soft);
      transform: scale(1.08) rotate(15deg);
    }

    .back-link {
      font-size: 13px;
      font-weight: 600;
      color: var(--muted);
      text-decoration: none;
      padding: 7px 13px;
      border-radius: var(--rx);
      border: 1.5px solid transparent;
      transition: all .2s;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .back-link:hover {
      color: var(--accent);
      border-color: var(--acc-brd);
      background: var(--acc-soft);
      transform: translateX(-2px);
    }

    /* ── PAGE ── */
    .page {
      max-width: 880px;
      margin: 0 auto;
      padding: 44px 24px 80px;
    }

    /* ── HEADER ── */
    .pg-header {
      text-align: center;
      margin-bottom: 36px;
      animation: fadeUp .6s .1s ease both;
    }

    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(16px);
      }

      to {
        opacity: 1;
        transform: none;
      }
    }

    .pg-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 15px;
      background: var(--acc-soft);
      border: 1.5px solid var(--acc-brd);
      border-radius: 100px;
      font-family: 'Space Mono', monospace;
      font-size: 11px;
      letter-spacing: .08em;
      color: var(--accent);
      margin-bottom: 14px;
      animation: fadeUp .5s .2s ease both;
    }

    .pg-title {
      font-family: 'Playfair Display', serif;
      font-size: clamp(28px, 5vw, 46px);
      font-weight: 400;
      letter-spacing: -.035em;
      line-height: 1.08;
      color: var(--ink);
      margin-bottom: 10px;
      animation: fadeUp .6s .3s ease both;
    }

    .pg-title em {
      font-style: italic;
      color: var(--accent);
    }

    .pg-sub {
      font-size: 15px;
      font-weight: 400;
      color: var(--muted);
      max-width: 400px;
      margin: 0 auto;
      line-height: 1.65;
      animation: fadeUp .6s .4s ease both;
    }

    /* ── MODE TABS ── */
    .tabs {
      display: flex;
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--r);
      box-shadow: var(--sh);
      overflow: hidden;
      margin-bottom: 14px;
      animation: fadeUp .5s .45s ease both;
    }

    .tab {
      flex: 1;
      padding: 13px 20px;
      background: transparent;
      border: none;
      border-right: 1.5px solid var(--border);
      font-family: 'Inter', sans-serif;
      font-size: 13.5px;
      font-weight: 600;
      color: var(--muted);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all .18s;
      position: relative;
      overflow: hidden;
    }

    .tab:last-child {
      border-right: none;
    }

    .tab.on {
      background: var(--acc-soft);
      color: var(--accent);
    }

    .tab.on::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 60%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(26, 61, 228, .08), transparent);
      animation: tabShine 2.5s .5s ease-in-out infinite;
    }

    @keyframes tabShine {
      0% {
        left: -100%;
      }

      60%,
      100% {
        left: 160%;
      }
    }

    .tab:hover:not(.on) {
      background: var(--s2);
      color: var(--ink2);
    }

    .tab-badge {
      font-family: 'Space Mono', monospace;
      font-size: 10px;
      padding: 2px 8px;
      border-radius: 100px;
      background: var(--s2);
      color: var(--faint);
      transition: all .18s;
    }

    .tab.on .tab-badge {
      background: var(--acc-brd);
      color: var(--accent);
    }

    /* ── CARD ── */
    .card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 20px;
      box-shadow: var(--sh);
      overflow: hidden;
      margin-bottom: 14px;
      animation: fadeUp .5s .5s ease both;
      transition: border-color .2s, box-shadow .2s;
    }

    .card:focus-within {
      border-color: var(--acc-brd);
      box-shadow: 0 0 0 4px rgba(26, 61, 228, .08), var(--sh);
    }

    .card-head {
      padding: 15px 20px;
      border-bottom: 1.5px solid var(--border);
      display: flex;
      align-items: center;
      gap: 10px;
      background: var(--surface);
    }

    .card-ico {
      width: 30px;
      height: 30px;
      background: var(--acc-soft);
      border: 1.5px solid var(--acc-brd);
      border-radius: 9px;
      display: grid;
      place-items: center;
      font-size: 14px;
      transition: transform .3s cubic-bezier(.34, 1.56, .64, 1);
    }

    .card:focus-within .card-ico {
      transform: scale(1.1) rotate(-5deg);
    }

    .card-title {
      font-size: 14px;
      font-weight: 700;
      color: var(--ink);
    }

    .card-sub {
      font-size: 12px;
      color: var(--muted);
      margin-top: 1px;
    }

    textarea {
      width: 100%;
      background: transparent;
      border: none;
      resize: none;
      color: var(--ink2);
      font-family: 'Space Mono', monospace;
      font-size: 13px;
      line-height: 1.8;
      padding: 20px;
      outline: none;
      min-height: 200px;
    }

    textarea::placeholder {
      color: var(--faint);
    }

    .ta-foot {
      padding: 10px 20px;
      border-top: 1.5px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-family: 'Space Mono', monospace;
      font-size: 11px;
      color: var(--faint);
      background: var(--s2);
    }

    .wn {
      font-weight: 700;
      transition: color .2s;
    }

    /* ── FILE PANEL ── */
    .file-panel {
      display: none;
    }

    .file-panel.on {
      display: block;
      animation: fadeUp .3s ease both;
    }

    .text-panel.off {
      display: none;
    }

    .drop {
      border: 2px dashed var(--acc-brd);
      border-radius: 20px;
      background: var(--surface);
      box-shadow: var(--sh);
      padding: 56px 32px;
      text-align: center;
      cursor: pointer;
      transition: all .25s;
      margin-bottom: 14px;
      position: relative;
    }

    .drop:hover,
    .drop.drag {
      border-color: var(--accent);
      background: var(--acc-soft);
      transform: scale(1.01);
      box-shadow: var(--sh2);
    }

    .drop input {
      position: absolute;
      inset: 0;
      opacity: 0;
      cursor: pointer;
      width: 100%;
      height: 100%;
    }

    .drop-ico {
      width: 56px;
      height: 56px;
      background: var(--acc-soft);
      border: 1.5px solid var(--acc-brd);
      border-radius: 16px;
      display: grid;
      place-items: center;
      font-size: 24px;
      margin: 0 auto 18px;
      transition: all .3s cubic-bezier(.34, 1.56, .64, 1);
    }

    .drop:hover .drop-ico,
    .drop.drag .drop-ico {
      background: var(--accent);
      border-color: var(--accent);
      transform: scale(1.12) rotate(-6deg);
    }

    .drop-title {
      font-size: 15px;
      font-weight: 700;
      color: var(--ink);
      margin-bottom: 6px;
    }

    .drop-sub {
      font-size: 13px;
      color: var(--muted);
      line-height: 1.6;
      margin-bottom: 16px;
    }

    .drop-types {
      display: inline-flex;
      gap: 7px;
    }

    .type-tag {
      font-family: 'Space Mono', monospace;
      font-size: 11px;
      font-weight: 500;
      padding: 4px 11px;
      border-radius: 100px;
      border: 1.5px solid var(--acc-brd);
      background: var(--acc-soft);
      color: var(--accent);
      transition: all .2s;
    }

    .drop:hover .type-tag {
      background: var(--accent);
      border-color: var(--accent);
      color: #fff;
    }

    .fprev {
      display: none;
      background: var(--acc-soft);
      border: 1.5px solid var(--acc-brd);
      border-radius: var(--r);
      box-shadow: var(--sh);
      padding: 16px 20px;
      align-items: center;
      gap: 14px;
      margin-bottom: 14px;
    }

    .fprev.on {
      display: flex;
      animation: fadeUp .3s ease both;
    }

    .fprev-ico {
      width: 42px;
      height: 42px;
      border-radius: 11px;
      display: grid;
      place-items: center;
      font-size: 20px;
      flex-shrink: 0;
      border: 1.5px solid var(--acc-brd);
      background: var(--surface);
    }

    .fprev-info {
      flex: 1;
      min-width: 0;
    }

    .fprev-name {
      font-size: 14px;
      font-weight: 700;
      color: var(--ink);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .fprev-size {
      font-family: 'Space Mono', monospace;
      font-size: 11px;
      color: var(--muted);
      margin-top: 2px;
    }

    .frem {
      width: 30px;
      height: 30px;
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 8px;
      display: grid;
      place-items: center;
      cursor: pointer;
      font-size: 14px;
      color: var(--muted);
      flex-shrink: 0;
      transition: all .2s;
    }

    .frem:hover {
      background: var(--dbg);
      border-color: var(--dbrd);
      color: var(--danger);
      transform: scale(1.1) rotate(90deg);
    }

    /* ── ERR ── */
    .err {
      display: none;
      padding: 13px 16px;
      background: var(--dbg);
      border: 1.5px solid var(--dbrd);
      border-radius: var(--rs);
      font-size: 13px;
      font-weight: 600;
      color: var(--danger);
      margin-bottom: 12px;
      gap: 8px;
      align-items: start;
      line-height: 1.55;
    }

    .err.on {
      display: flex;
      animation: shake .4s ease;
    }

    @keyframes shake {

      0%,
      100% {
        transform: none;
      }

      20% {
        transform: translateX(-7px);
      }

      40% {
        transform: translateX(7px);
      }

      60% {
        transform: translateX(-4px);
      }

      80% {
        transform: translateX(4px);
      }
    }

    /* ── RUN BTN ── */
    .run-btn {
      width: 100%;
      height: 56px;
      background: var(--accent);
      border: none;
      border-radius: var(--r);
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      font-weight: 400;
      letter-spacing: -.01em;
      color: #fff;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      transition: all .22s;
      animation: fadeUp .5s .6s ease both;
      position: relative;
      overflow: hidden;
    }

    .run-btn::before {
      content: '';
      position: absolute;
      inset: 0;
      background: rgba(255, 255, 255, .18);
      transform: translateX(-120%) skewX(-15deg);
      transition: transform .45s ease;
    }

    .run-btn:hover::before {
      transform: translateX(140%) skewX(-15deg);
    }

    .run-btn:hover {
      background: var(--ink);
      transform: translateY(-2px);
      box-shadow: 0 12px 32px rgba(26, 61, 228, .28);
    }

    .run-btn:active {
      transform: scale(.99);
    }

    .run-btn:disabled {
      opacity: .4;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    /* ── LOADING ── */
    .loading {
      display: none;
      flex-direction: column;
      align-items: center;
      padding: 96px 24px;
      gap: 0;
    }

    .loading.on {
      display: flex;
      animation: fadeUp .4s ease forwards;
    }

    .scan-wrap {
      position: relative;
      width: 116px;
      height: 116px;
      margin-bottom: 32px;
      flex-shrink: 0;
    }

    .s-arc {
      position: absolute;
      border-radius: 50%;
      border: 2px solid transparent;
    }

    .s-arc-1 {
      inset: 0;
      border-top-color: var(--accent);
      border-right-color: rgba(26, 61, 228, .25);
      animation: arcSpin 1.1s linear infinite;
      filter: drop-shadow(0 0 8px rgba(26, 61, 228, .45));
    }

    .s-arc-2 {
      inset: 11px;
      border-top-color: var(--acc2);
      border-left-color: rgba(68, 102, 245, .2);
      animation: arcSpin .8s linear infinite reverse;
    }

    .s-arc-3 {
      inset: 22px;
      border-top-color: var(--acc-brd);
      animation: arcSpin .55s linear infinite;
    }

    @keyframes arcSpin {
      to {
        transform: rotate(360deg);
      }
    }

    .s-orbit {
      position: absolute;
      inset: 4px;
      animation: arcSpin 2.2s linear infinite;
    }

    .s-dot {
      position: absolute;
      top: 0;
      left: 50%;
      width: 9px;
      height: 9px;
      margin-left: -4.5px;
      background: var(--accent);
      border-radius: 50%;
      box-shadow: 0 0 10px rgba(26, 61, 228, .7);
    }

    .s-center {
      position: absolute;
      inset: 34px;
      border-radius: 50%;
      background: var(--acc-soft);
      border: 1.5px solid var(--acc-brd);
      display: grid;
      place-items: center;
      font-size: 20px;
      animation: radar 1.8s ease-in-out infinite;
    }

    @keyframes radar {

      0%,
      100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(26, 61, 228, .2);
      }

      50% {
        transform: scale(1.08);
        box-shadow: 0 0 0 10px rgba(26, 61, 228, 0);
      }
    }

    .ld-text {
      text-align: center;
      font-family: 'Space Mono', monospace;
      font-size: 12px;
      color: var(--muted);
    }

    .ld-text strong {
      display: block;
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      font-weight: 700;
      color: var(--ink2);
      margin-bottom: 5px;
      animation: phPop .4s ease;
    }

    @keyframes phPop {
      from {
        opacity: 0;
        transform: translateY(5px);
      }

      to {
        opacity: 1;
        transform: none;
      }
    }

    .ld-segs {
      display: flex;
      gap: 5px;
      margin-top: 18px;
      justify-content: center;
    }

    .ld-seg {
      width: 30px;
      height: 3px;
      background: var(--border);
      border-radius: 3px;
      overflow: hidden;
      position: relative;
    }

    .ld-seg.lit::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(90deg, var(--accent), var(--acc2));
      border-radius: 3px;
      animation: segIn .35s ease forwards;
    }

    @keyframes segIn {
      from {
        transform: scaleX(0);
      }

      to {
        transform: scaleX(1);
      }
    }

    .ld-dots {
      display: flex;
      gap: 5px;
      margin-top: 10px;
      justify-content: center;
    }

    .ld-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: var(--acc-brd);
      animation: dotB 1.3s ease-in-out infinite;
    }

    .ld-dot:nth-child(2) {
      animation-delay: .2s;
    }

    .ld-dot:nth-child(3) {
      animation-delay: .4s;
    }

    @keyframes dotB {

      0%,
      80%,
      100% {
        transform: scale(.8);
        background: var(--acc-brd);
      }

      40% {
        transform: scale(1.3);
        background: var(--accent);
      }
    }

    /* ── RESULTS ── */
    .results {
      display: none;
    }

    .results.on {
      display: block;
      animation: fadeUp .5s ease;
    }

    /* Gauge card */
    .origin-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 20px;
      box-shadow: var(--sh);
      padding: 30px;
      display: flex;
      gap: 30px;
      align-items: center;
      margin-bottom: 14px;
      flex-wrap: wrap;
      animation: fadeUp .4s ease both;
    }

    .gauge-wrap {
      position: relative;
      width: 124px;
      height: 124px;
      flex-shrink: 0;
    }

    .g-svg {
      transform: rotate(-90deg);
    }

    .g-bg {
      fill: none;
      stroke: var(--s2);
      stroke-width: 11;
    }

    .g-fill {
      fill: none;
      stroke-width: 11;
      stroke-linecap: round;
      stroke-dasharray: 310;
      stroke-dashoffset: 310;
      transition: stroke-dashoffset 1.6s cubic-bezier(.4, 0, .2, 1);
      filter: drop-shadow(0 0 5px rgba(26, 61, 228, .25));
    }

    .g-center {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .g-num {
      font-family: 'Playfair Display', serif;
      font-size: 30px;
      font-weight: 400;
      line-height: 1;
      letter-spacing: -.02em;
    }

    .g-lbl {
      font-family: 'Space Mono', monospace;
      font-size: 9px;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: .1em;
      margin-top: 4px;
    }

    .o-verdict {
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      font-weight: 400;
      letter-spacing: -.02em;
      margin-bottom: 8px;
      line-height: 1.15;
    }

    .o-desc {
      font-size: 14px;
      color: var(--muted);
      line-height: 1.7;
      margin-bottom: 16px;
    }

    .mpills {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .mpill {
      font-family: 'Space Mono', monospace;
      font-size: 11px;
      padding: 4px 11px;
      border-radius: 100px;
      border: 1.5px solid var(--acc-brd);
      background: var(--acc-soft);
      color: var(--accent);
      animation: pPop .4s cubic-bezier(.34, 1.56, .64, 1) both;
      transition: transform .2s;
    }

    .mpill:hover {
      transform: scale(1.05);
    }

    .mpill:nth-child(1) {
      animation-delay: .1s;
    }

    .mpill:nth-child(2) {
      animation-delay: .2s;
    }

    .mpill:nth-child(3) {
      animation-delay: .3s;
    }

    @keyframes pPop {
      from {
        opacity: 0;
        transform: scale(.8);
      }

      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    /* Score tiles */
    .score-row {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 12px;
      margin-bottom: 14px;
    }

    .s-tile {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--r);
      box-shadow: var(--sh);
      padding: 22px 20px;
      text-align: center;
      transition: all .25s;
      animation: fadeUp .4s ease both;
    }

    .s-tile:nth-child(1) {
      animation-delay: .08s;
    }

    .s-tile:nth-child(2) {
      animation-delay: .16s;
    }

    .s-tile:nth-child(3) {
      animation-delay: .24s;
    }

    .s-tile:hover {
      transform: translateY(-3px);
      box-shadow: var(--sh2);
      border-color: var(--acc-brd);
    }

    .tile-v {
      font-family: 'Playfair Display', serif;
      font-size: 40px;
      font-weight: 400;
      letter-spacing: -.03em;
      line-height: 1;
      margin-bottom: 6px;
    }

    .tile-l {
      font-size: 11px;
      font-weight: 600;
      font-family: 'Space Mono', monospace;
      text-transform: uppercase;
      letter-spacing: .09em;
      color: var(--muted);
    }

    /* Section cards */
    .sec-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 20px;
      box-shadow: var(--sh);
      overflow: hidden;
      margin-bottom: 14px;
      animation: fadeUp .4s ease both;
      transition: box-shadow .25s;
    }

    .sec-card:hover {
      box-shadow: var(--sh2);
    }

    .sec-head {
      padding: 13px 22px;
      border-bottom: 1.5px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .09em;
      text-transform: uppercase;
      color: var(--muted);
      background: var(--s2);
      font-family: 'Space Mono', monospace;
    }

    .sec-r {
      font-size: 11px;
      color: var(--faint);
      text-transform: none;
      letter-spacing: 0;
      font-weight: 400;
    }

    .anno-body {
      padding: 22px;
      font-family: 'Space Mono', monospace;
      font-size: 13px;
      line-height: 2.1;
      color: var(--ink2);
      white-space: pre-wrap;
      word-break: break-word;
    }

    mark.plag {
      background: rgba(212, 32, 32, .1);
      color: #b91c1c;
      border-radius: 3px;
      padding: 1px 4px;
      cursor: pointer;
      border-bottom: 2.5px solid #fca5a5;
      transition: all .2s;
      animation: mIn .35s ease both;
    }

    @keyframes mIn {
      from {
        background: transparent;
        border-bottom-color: transparent;
      }

      to {
        background: rgba(212, 32, 32, .1);
        border-bottom-color: #fca5a5;
      }
    }

    mark.plag:hover {
      background: rgba(212, 32, 32, .16);
      transform: scale(1.02);
    }

    .legend {
      padding: 10px 22px;
      border-top: 1.5px solid var(--border);
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
      background: var(--s2);
    }

    .leg {
      display: flex;
      align-items: center;
      gap: 6px;
      font-family: 'Space Mono', monospace;
      font-size: 10.5px;
      color: var(--muted);
    }

    .leg-d {
      width: 10px;
      height: 10px;
      border-radius: 3px;
    }

    /* Sources */
    .src-item {
      padding: 18px 22px;
      border-bottom: 1.5px solid var(--border);
      display: grid;
      grid-template-columns: auto 1fr auto;
      gap: 16px;
      align-items: start;
      transition: background .2s;
      animation: fadeUp .35s ease both;
    }

    .src-item:last-child {
      border-bottom: none;
    }

    .src-item:hover {
      background: var(--s2);
    }

    .src-rank {
      font-family: 'Space Mono', monospace;
      font-size: 11px;
      color: var(--faint);
      min-width: 26px;
      padding-top: 2px;
      font-weight: 500;
    }

    .src-title {
      font-size: 14px;
      font-weight: 700;
      color: var(--ink);
      margin-bottom: 3px;
      line-height: 1.4;
    }

    .src-url {
      font-family: 'Space Mono', monospace;
      font-size: 11px;
      color: var(--accent);
      text-decoration: none;
      display: block;
      margin-bottom: 6px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 520px;
    }

    .src-url:hover {
      text-decoration: underline;
    }

    .src-desc {
      font-size: 12.5px;
      color: var(--muted);
      line-height: 1.6;
      margin-bottom: 8px;
    }

    .src-seqs {
      display: flex;
      flex-direction: column;
      gap: 5px;
      margin-top: 8px;
    }

    .src-seq {
      font-family: 'Space Mono', monospace;
      font-size: 11px;
      color: #b91c1c;
      background: rgba(212, 32, 32, .07);
      border-left: 2.5px solid rgba(212, 32, 32, .3);
      padding: 5px 11px;
      border-radius: 0 5px 5px 0;
      font-style: italic;
      transition: border-left-width .2s, padding-left .2s;
    }

    .src-seq:hover {
      border-left-width: 4px;
      padding-left: 13px;
    }

    [data-theme="dark"] .src-seq {
      color: #fca5a5;
      background: rgba(248, 113, 113, .08);
      border-left-color: rgba(248, 113, 113, .2);
    }

    .src-score {
      text-align: right;
      flex-shrink: 0;
    }

    .src-pct {
      font-family: 'Playfair Display', serif;
      font-size: 24px;
      font-weight: 400;
      letter-spacing: -.02em;
      color: var(--danger);
      line-height: 1;
    }

    .src-pct-l {
      font-family: 'Space Mono', monospace;
      font-size: 9px;
      color: var(--faint);
      text-transform: uppercase;
      letter-spacing: .07em;
      margin-top: 3px;
    }

    .src-w {
      font-family: 'Space Mono', monospace;
      font-size: 10px;
      color: var(--faint);
      margin-top: 4px;
    }

    .cite-tag {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-family: 'Space Mono', monospace;
      font-size: 10px;
      font-weight: 500;
      padding: 2px 9px;
      background: var(--okbg);
      border: 1.5px solid var(--okbrd);
      color: var(--ok);
      border-radius: 100px;
      margin-left: 7px;
      vertical-align: middle;
    }

    /* Credits */
    .cred-bar {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--r);
      box-shadow: var(--sh);
      padding: 14px 22px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 14px;
      font-size: 13px;
      color: var(--muted);
      flex-wrap: wrap;
      gap: 8px;
      animation: fadeUp .4s ease both;
    }

    .cred-used {
      font-family: 'Space Mono', monospace;
      font-size: 11px;
      padding: 3px 11px;
      background: var(--wbg);
      border: 1.5px solid var(--wbrd);
      color: var(--warn);
      border-radius: 100px;
    }

    .cred-left {
      font-family: 'Space Mono', monospace;
      font-size: 11px;
      padding: 3px 11px;
      background: var(--okbg);
      border: 1.5px solid var(--okbrd);
      color: var(--ok);
      border-radius: 100px;
    }

    .empty {
      padding: 44px 24px;
      text-align: center;
      font-size: 14px;
      color: var(--muted);
    }

    /* Reset */
    .reset-btn {
      width: 100%;
      height: 46px;
      background: transparent;
      border: 1.5px solid var(--border);
      border-radius: var(--r);
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      font-weight: 600;
      color: var(--muted);
      cursor: pointer;
      transition: all .2s;
      position: relative;
      overflow: hidden;
    }

    .reset-btn::before {
      content: '';
      position: absolute;
      inset: 0;
      background: var(--acc-soft);
      transform: scaleX(0);
      transform-origin: left;
      transition: transform .3s ease;
    }

    .reset-btn:hover {
      border-color: var(--accent);
      color: var(--accent);
    }

    .reset-btn:hover::before {
      transform: scaleX(1);
    }

    .reset-btn span {
      position: relative;
      z-index: 1;
    }

    @media(max-width:640px) {
      .topbar {
        padding: 0 16px;
      }

      .page {
        padding: 24px 16px 60px;
      }

      .score-row {
        grid-template-columns: 1fr 1fr;
      }

      .origin-card {
        flex-direction: column;
        gap: 20px;
      }

      .src-item {
        grid-template-columns: auto 1fr;
      }

      .src-score {
        display: none;
      }

      .tab .tab-badge {
        display: none;
      }
    }
  </style>
</head>

<body>

  <div id="spb"></div>

  <nav class="topbar">
    <a class="brand" href="landing.php">
      <div class="brand-ico">🔍</div>
      <div class="brand-name">Plagia<em>Scope</em></div>
    </a>
    <div class="top-r">
      <button class="dm-btn" id="dmBtn">🌙</button>
      <a class="back-link" href="landing.php">← Home</a>
    </div>
  </nav>

  <div class="page">

    <div class="pg-header">
      <div class="pg-eyebrow">✦ BaiSQL AI · English Research</div>
      <h1 class="pg-title">Research <em>Integrity</em> Checker</h1>
      <p class="pg-sub">Paste your text or upload a file — get your plagiarism report in seconds.</p>
    </div>

    <div id="inputSection">

      <div class="tabs">
        <button class="tab on" id="tabText" onclick="switchMode('text')">
          ✏️ Paste Text <span class="tab-badge">TXT</span>
        </button>
        <button class="tab" id="tabFile" onclick="switchMode('file')">
          📎 Upload File <span class="tab-badge">PDF · DOC · DOCX</span>
        </button>
      </div>

      <div class="text-panel" id="textPanel">
        <div class="card">
          <div class="card-head">
            <div class="card-ico">📝</div>
            <div>
              <div class="card-title">Research Text</div>
              <div class="card-sub">Min 100 characters · Max 120,000 characters</div>
            </div>
          </div>
          <textarea id="mainText" placeholder="Paste your research text here…" oninput="updateCount()"></textarea>
          <div class="ta-foot">
            <span><span class="wn" id="wNum">0</span> words · <span id="cNum">0</span> chars</span>
            <span id="charHint">100 chars minimum</span>
          </div>
        </div>
      </div>

      <div class="file-panel" id="filePanel">
        <div class="drop" id="dropZone">
          <input type="file" id="fileInput" accept=".pdf,.doc,.docx" onchange="onFile(event)">
          <div class="drop-ico">📄</div>
          <div class="drop-title">Drop file here or click to browse</div>
          <div class="drop-sub">PDF, DOC, or DOCX · Max 10 MB</div>
          <div class="drop-types">
            <span class="type-tag">PDF</span>
            <span class="type-tag">DOC</span>
            <span class="type-tag">DOCX</span>
          </div>
        </div>
        <div class="fprev" id="fprev">
          <div class="fprev-ico" id="fprevIco">📄</div>
          <div class="fprev-info">
            <div class="fprev-name" id="fprevName">—</div>
            <div class="fprev-size" id="fprevSize">—</div>
          </div>
          <div class="frem" onclick="removeFile()" title="Remove">✕</div>
        </div>
      </div>

      <div class="err" id="errBanner"><span>⚠</span><span id="errMsg"></span></div>

      <button class="run-btn" onclick="analyze()">🔍 Analyze for Plagiarism</button>
    </div>

    <!-- Loading -->
    <div class="loading" id="loadWrap">
      <div class="scan-wrap">
        <div class="s-arc s-arc-1"></div>
        <div class="s-arc s-arc-2"></div>
        <div class="s-arc s-arc-3"></div>
        <div class="s-orbit">
          <div class="s-dot"></div>
        </div>
        <div class="s-center">🔍</div>
      </div>
      <div class="ld-text">
        <strong id="ldPhase">Connecting to BaiSQL AI…</strong>
        Scanning against 400 billion sources
        <div class="ld-dots">
          <div class="ld-dot"></div>
          <div class="ld-dot"></div>
          <div class="ld-dot"></div>
        </div>
      </div>
      <div class="ld-segs" id="ldSegs">
        <div class="ld-seg"></div>
        <div class="ld-seg"></div>
        <div class="ld-seg"></div>
        <div class="ld-seg"></div>
        <div class="ld-seg"></div>
        <div class="ld-seg"></div>
      </div>
    </div>

    <!-- Results -->
    <div class="results" id="resultsSection">

      <div class="origin-card">
        <div class="gauge-wrap">
          <svg class="g-svg" width="124" height="124" viewBox="0 0 124 124">
            <circle class="g-bg" cx="62" cy="62" r="49" />
            <circle class="g-fill" id="gFill" cx="62" cy="62" r="49" />
          </svg>
          <div class="g-center">
            <div class="g-num" id="gNum">0%</div>
            <div class="g-lbl">Original</div>
          </div>
        </div>
        <div>
          <div class="o-verdict" id="verdict">—</div>
          <div class="o-desc" id="odesc">—</div>
          <div class="mpills" id="mpills"></div>
        </div>
      </div>

      <div class="score-row" id="scoreRow"></div>

      <div class="cred-bar" id="credBar" style="display:none">
        <span>Scan complete</span>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <span id="credUsed" class="cred-used"></span>
          <span id="credLeft" class="cred-left"></span>
        </div>
      </div>

      <div class="sec-card" id="annoCard">
        <div class="sec-head">
          <span>📄 Annotated Text</span>
          <span class="sec-r" id="annoSub"></span>
        </div>
        <div class="anno-body" id="annoBody"></div>
        <div class="legend">
          <div class="leg"><span class="leg-d" style="background:#fca5a5;border:1px solid #f87171"></span> Plagiarized passage</div>
        </div>
      </div>

      <div class="sec-card">
        <div class="sec-head">
          <span>🔗 Matching Sources</span>
          <span class="sec-r" id="srcCount"></span>
        </div>
        <div id="srcList"></div>
      </div>

      <button class="reset-btn" onclick="resetTool()"><span>← Analyze another text</span></button>
    </div>

  </div>

  <script>
    // ── Dark mode ──
    const htmlEl = document.documentElement;
    const dmBtn = document.getElementById('dmBtn');
    const saved = localStorage.getItem('ps-theme');
    if (saved) {
      htmlEl.setAttribute('data-theme', saved);
      dmBtn.textContent = saved === 'dark' ? '☀️' : '🌙';
    }
    dmBtn.addEventListener('click', () => {
      const next = htmlEl.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      htmlEl.setAttribute('data-theme', next);
      dmBtn.textContent = next === 'dark' ? '☀️' : '🌙';
      localStorage.setItem('ps-theme', next);
    });

    // ── Scroll progress ──
    window.addEventListener('scroll', () => {
      const tot = document.documentElement.scrollHeight - window.innerHeight;
      document.getElementById('spb').style.width = (window.scrollY / tot * 100) + '%';
    });

    function getConfig() {
      try {
        return PLAGIASCOPE_CONFIG || {};
      } catch (e) {
        return {};
      }
    }

    let mode = 'text',
      selFile = null;

    function switchMode(m) {
      mode = m;
      hideErr();
      document.getElementById('tabText').classList.toggle('on', m === 'text');
      document.getElementById('tabFile').classList.toggle('on', m === 'file');
      document.getElementById('textPanel').classList.toggle('off', m !== 'text');
      document.getElementById('filePanel').classList.toggle('on', m === 'file');
    }

    function onFile(e) {
      const f = e.target.files[0];
      if (f) setFile(f);
    }

    function setFile(file) {
      const ext = file.name.split('.').pop().toLowerCase();
      if (!['pdf', 'doc', 'docx'].includes(ext)) {
        showErr('Only PDF, DOC, and DOCX are supported.');
        return;
      }
      if (file.size > 10 * 1024 * 1024) {
        showErr('File must be under 10 MB.');
        return;
      }
      selFile = file;
      hideErr();
      document.getElementById('dropZone').style.display = 'none';
      document.getElementById('fprev').classList.add('on');
      document.getElementById('fprevIco').textContent = ext === 'pdf' ? '📕' : '📘';
      document.getElementById('fprevName').textContent = file.name;
      document.getElementById('fprevSize').textContent = fmtBytes(file.size);
    }

    function removeFile() {
      selFile = null;
      document.getElementById('fileInput').value = '';
      document.getElementById('fprev').classList.remove('on');
      document.getElementById('dropZone').style.display = '';
    }

    function fmtBytes(b) {
      if (b < 1024) return b + ' B';
      if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
      return (b / 1048576).toFixed(1) + ' MB';
    }

    const dz = document.getElementById('dropZone');
    dz.addEventListener('dragover', e => {
      e.preventDefault();
      dz.classList.add('drag');
    });
    dz.addEventListener('dragleave', () => dz.classList.remove('drag'));
    dz.addEventListener('drop', e => {
      e.preventDefault();
      dz.classList.remove('drag');
      const f = e.dataTransfer.files[0];
      if (f) setFile(f);
    });

    function updateCount() {
      const t = document.getElementById('mainText').value;
      const w = t.trim() ? t.trim().split(/\s+/).length : 0;
      document.getElementById('wNum').textContent = w;
      document.getElementById('cNum').textContent = t.length;
      const wn = document.getElementById('wNum');
      wn.style.color = t.length >= 100 ? 'var(--ok)' : t.length > 50 ? 'var(--warn)' : 'var(--ink2)';
      document.getElementById('charHint').textContent = t.length >= 100 ? '✓ Ready to scan' : '100 chars minimum';
    }

    function showErr(msg) {
      document.getElementById('errMsg').textContent = msg;
      const b = document.getElementById('errBanner');
      b.classList.remove('on');
      void b.offsetWidth;
      b.classList.add('on');
    }

    function hideErr() {
      document.getElementById('errBanner').classList.remove('on');
    }

    const phases = ['Connecting to BaiSQL AI…', 'Uploading to scan engine…', 'Comparing text sequences…', 'Identifying matching passages…', 'Scoring originality…', 'Building your report…'];
    let phIdx = 0,
      phTimer, segIdx = 0;

    function startLoading() {
      phIdx = 0;
      segIdx = 0;
      document.querySelectorAll('.ld-seg').forEach(s => s.classList.remove('lit'));
      setPhase();
      phTimer = setInterval(() => {
        phIdx = (phIdx + 1) % phases.length;
        setPhase();
        const segs = document.querySelectorAll('.ld-seg');
        if (segIdx < segs.length) {
          segs[segIdx].classList.add('lit');
          segIdx++;
        }
      }, 2200);
    }

    function setPhase() {
      const el = document.getElementById('ldPhase');
      el.style.animation = 'none';
      void el.offsetWidth;
      el.style.animation = '';
      el.textContent = phases[phIdx];
    }

    function stopLoading() {
      clearInterval(phTimer);
    }

    async function analyze() {
      hideErr();
      const cfg = getConfig();
      const key = cfg.apiKey || '';
      if (!key) {
        showErr('API key not configured. Edit config.js.');
        return;
      }

      let opts;
      if (mode === 'file') {
        if (!selFile) {
          showErr('Please select a file to scan.');
          return;
        }
        const fd = new FormData();
        fd.append('_apiKey', key);
        fd.append('language', cfg.language || 'en');
        fd.append('country', cfg.country || 'us');
        fd.append('file', selFile, selFile.name);
        opts = {
          method: 'POST',
          body: fd
        };
      } else {
        const text = document.getElementById('mainText').value.trim();
        if (!text) {
          showErr('Please paste some text to analyze.');
          return;
        }
        if (text.length < 100) {
          showErr('Text must be at least 100 characters.');
          return;
        }
        if (text.length > 120000) {
          showErr('Text exceeds 120,000 characters.');
          return;
        }
        opts = {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            _apiKey: key,
            text,
            language: cfg.language || 'en',
            country: cfg.country || 'us'
          })
        };
      }

      document.getElementById('inputSection').style.display = 'none';
      document.getElementById('loadWrap').classList.add('on');
      document.getElementById('resultsSection').classList.remove('on');
      startLoading();

      try {
        const res = await fetch('proxy.php', opts);
        const data = await res.json();
        if (!res.ok) throw new Error(data?.message || data?.error || `API error ${res.status}`);
        stopLoading();
        const txt = data.text || (mode === 'text' ? document.getElementById('mainText').value.trim() : '');
        renderResults(data, txt);
      } catch (err) {
        stopLoading();
        document.getElementById('loadWrap').classList.remove('on');
        document.getElementById('inputSection').style.display = 'block';
        showErr('Error: ' + (err.message || 'Unexpected error. Check config.js.'));
      }
    }

    function renderResults(data, origText) {
      document.getElementById('loadWrap').classList.remove('on');
      document.getElementById('resultsSection').classList.add('on');

      const result = data.result || {};
      const sources = data.sources || [];
      const indexes = data.indexes || [];
      const plagScore = result.score ?? 0;
      const origScore = Math.max(0, 100 - plagScore);
      const strokeColor = origScore >= 80 ? '#157a3a' : origScore >= 60 ? '#c47a00' : '#d42020';

      const gFill = document.getElementById('gFill');
      gFill.style.stroke = strokeColor;
      setTimeout(() => {
        gFill.style.strokeDashoffset = 310 - (origScore / 100) * 310;
      }, 80);

      let cur = 0;
      const nEl = document.getElementById('gNum');
      nEl.style.color = strokeColor;
      const t = setInterval(() => {
        cur = Math.min(cur + 1.5, origScore);
        nEl.textContent = Math.round(cur) + '%';
        if (cur >= origScore) clearInterval(t);
      }, 16);

      const verdict = origScore >= 85 ? 'Highly Original' : origScore >= 70 ? 'Mostly Original' : origScore >= 50 ? 'Partially Plagiarized' : 'High Plagiarism Risk';
      const desc = origScore >= 85 ? 'Your text appears largely original.' : origScore >= 70 ? 'Some matching content found. Review highlighted passages.' : origScore >= 50 ? 'Significant matches detected. Revise and cite sources.' : 'Large portion matches existing sources. Extensive revision required.';

      document.getElementById('verdict').textContent = verdict;
      document.getElementById('odesc').textContent = desc;

      const pEl = document.getElementById('mpills');
      pEl.innerHTML = '';
      [`${result.sourceCounts??sources.length} sources matched`, `${result.textWordCounts??'—'} words scanned`, `${result.totalPlagiarismWords??0} plagiarized words`].forEach(l => {
        const e = document.createElement('span');
        e.className = 'mpill';
        e.textContent = l;
        pEl.appendChild(e);
      });

      const sRow = document.getElementById('scoreRow');
      sRow.innerHTML = '';
      [{
          val: plagScore + '%',
          label: 'Plagiarism Score',
          color: plagScore > 30 ? '#d42020' : '#157a3a'
        },
        {
          val: result.identicalWordCounts ?? 0,
          label: 'Identical Words',
          color: '#c47a00'
        },
        {
          val: result.similarWordCounts ?? 0,
          label: 'Similar Words',
          color: '#1a3de4'
        }
      ].forEach((t, i) => {
        const d = document.createElement('div');
        d.className = 's-tile';
        d.style.animationDelay = (i * .08) + 's';
        d.innerHTML = `<div class="tile-v" style="color:${t.color}">${t.val}</div><div class="tile-l">${t.label}</div>`;
        sRow.appendChild(d);
      });

      if (data.credits_used !== undefined) {
        document.getElementById('credBar').style.display = 'flex';
        document.getElementById('credUsed').textContent = `${data.credits_used} credits used`;
        document.getElementById('credLeft').textContent = `${data.credits_remaining} remaining`;
      }

      const aEl = document.getElementById('annoBody');
      if (origText && indexes.length > 0) {
        const sorted = [...indexes].sort((a, b) => a.startIndex - b.startIndex);
        let html = '',
          cur = 0;
        sorted.forEach(idx => {
          if (idx.startIndex > cur) html += esc(origText.slice(cur, idx.startIndex));
          html += `<mark class="plag" title="Plagiarized passage">${esc(origText.slice(idx.startIndex,idx.endIndex))}</mark>`;
          cur = idx.endIndex;
        });
        if (cur < origText.length) html += esc(origText.slice(cur));
        aEl.innerHTML = html;
        document.getElementById('annoSub').textContent = `${indexes.length} passage${indexes.length!==1?'s':''} flagged`;
      } else if (origText) {
        aEl.textContent = origText;
        document.getElementById('annoSub').textContent = 'No plagiarism detected';
      } else {
        document.getElementById('annoCard').style.display = 'none';
      }

      const sEl = document.getElementById('srcList');
      sEl.innerHTML = '';
      document.getElementById('srcCount').textContent = sources.length ? `${sources.length} source${sources.length!==1?'s':'`'}`:'' ;

  if(!sources.length){
    sEl.innerHTML='<div class="empty">✓ No matching sources found — your text appears original.</div>';
  }else{
    [...sources].sort((a,b)=>(b.score??0)-(a.score??0)).forEach((src,i)=>{
      const item=document.createElement('div');item.className='src-item';item.style.animationDelay=(i*.06)+'s';
      const sv=typeof src.score==='number'?src.score.toFixed(1)+'%':'—';
      const ct=src.citation?` < span class = "cite-tag" > ✓cited < /span>`:'';
      const sq = (src.plagiarismFound || []).slice(0, 3).map(p => `<div class="src-seq">"${esc(p.sequence)}"</div>`).join('');
      item.innerHTML = `
        <div class="src-rank">${String(i+1).padStart(2,'0')}</div>
        <div>
          <div class="src-title">${esc(src.title||'Untitled source')}${ct}</div>
          <a class="src-url" href="${esc(src.url||'#')}" target="_blank" rel="noopener">${esc(src.url||'')}</a>
          ${src.description?`<div class="src-desc">${esc(src.description)}</div>`:''}
          ${sq?`<div class="src-seqs">${sq}</div>`:''}
        </div>
        <div class="src-score">
          <div class="src-pct">${sv}</div>
          <div class="src-pct-l">match</div>
          <div class="src-w">${src.plagiarismWords??0} words</div>
        </div>`;
      sEl.appendChild(item);
    });
    }

    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
    }

    function esc(s) {
      return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function resetTool() {
      selFile = null;
      document.getElementById('fileInput').value = '';
      document.getElementById('fprev').classList.remove('on');
      document.getElementById('dropZone').style.display = '';
      document.getElementById('annoCard').style.display = '';
      document.getElementById('inputSection').style.display = 'block';
      document.getElementById('resultsSection').classList.remove('on');
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }
  </script>
</body>

</html>