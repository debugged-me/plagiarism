<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlagiaScope | AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
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
            --s2: #eeecE8;
            --border: #ddd9d0;
            --ink: #0e0c09;
            --ink2: #2a2620;
            --muted: #6b6560;
            --faint: #a09890;
            --accent: #1a3de4;
            --acc2: #4466f5;
            --acc-soft: #eaedff;
            --acc-brd: #b8c4fd;
            --danger: #d42020;
            --warn: #c47a00;
            --ok: #157a3a;
            --ok-bg: #ecfdf3;
            --ok-brd: #a7f3c8;
            --loader-bg: #f5f3ef;
            --nav-bg: rgba(245, 243, 239, 0.88);
            --sh: 0 2px 12px rgba(14, 12, 9, 0.08);
            --sh2: 0 8px 32px rgba(14, 12, 9, 0.1);
            --sh3: 0 24px 64px rgba(14, 12, 9, 0.12);
        }

        [data-theme="dark"] {
            --bg: #080a0f;
            --surface: #0f1119;
            --s2: #161b26;
            --border: #1f2840;
            --ink: #eceef8;
            --ink2: #bfc6dc;
            --muted: #6e7d9a;
            --faint: #3a4560;
            --accent: #5577ff;
            --acc2: #7a99ff;
            --acc-soft: #0e1530;
            --acc-brd: #1e3060;
            --danger: #f87171;
            --warn: #fbbf24;
            --ok: #34d375;
            --ok-bg: #042010;
            --ok-brd: #0d4020;
            --loader-bg: #080a0f;
            --nav-bg: rgba(8, 10, 15, 0.9);
            --sh: 0 2px 12px rgba(0, 0, 0, 0.35);
            --sh2: 0 8px 32px rgba(0, 0, 0, 0.45);
            --sh3: 0 24px 64px rgba(0, 0, 0, 0.55);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background: var(--bg);
            color: var(--ink);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            transition: background .4s, color .4s;
        }

        /* ─── LOADER ─── */
        #loader {
            position: fixed;
            inset: 0;
            background: var(--loader-bg);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 0;
            transition: opacity .7s ease, visibility .7s ease;
        }

        #loader.out {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        /* Pulsing background radial */
        #loader::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 55% 50% at 50% 50%, rgba(26, 61, 228, .07) 0%, transparent 70%);
            animation: ldBgPulse 2.5s ease-in-out infinite alternate;
            pointer-events: none;
        }

        @keyframes ldBgPulse {
            from {
                opacity: .5;
                transform: scale(.95);
            }

            to {
                opacity: 1;
                transform: scale(1.05);
            }
        }

        /* ── Orbital rings wrap ── */
        .ld-rings {
            position: relative;
            width: 110px;
            height: 110px;
            margin-bottom: 28px;
            animation: ldFade .5s .1s ease both;
        }

        .ld-ring {
            position: absolute;
            border-radius: 50%;
            border: 2px solid transparent;
        }

        .ld-ring-1 {
            inset: 0;
            border-top-color: var(--accent);
            border-right-color: rgba(26, 61, 228, .25);
            animation: ldSpin 1.1s linear infinite;
            filter: drop-shadow(0 0 8px rgba(26, 61, 228, .5));
        }

        .ld-ring-2 {
            inset: 12px;
            border-top-color: var(--acc2);
            border-left-color: rgba(68, 102, 245, .2);
            animation: ldSpin .8s linear infinite reverse;
        }

        .ld-ring-3 {
            inset: 24px;
            border-top-color: var(--acc-brd);
            animation: ldSpin .55s linear infinite;
        }

        @keyframes ldSpin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Orbiting dot */
        .ld-orbit {
            position: absolute;
            inset: 4px;
            animation: ldSpin 2.2s linear infinite;
        }

        .ld-orb-dot {
            position: absolute;
            top: 0;
            left: 50%;
            width: 9px;
            height: 9px;
            margin-left: -4.5px;
            background: var(--accent);
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(26, 61, 228, .8);
        }

        /* Center icon with radar pulse */
        .ld-center {
            position: absolute;
            inset: 34px;
            border-radius: 50%;
            background: var(--acc-soft);
            border: 1.5px solid var(--acc-brd);
            display: grid;
            place-items: center;
            font-size: 20px;
            animation: ldRadar 1.8s ease-in-out infinite;
        }

        @keyframes ldRadar {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(26, 61, 228, .2);
            }

            50% {
                transform: scale(1.1);
                box-shadow: 0 0 0 12px rgba(26, 61, 228, 0);
            }
        }

        /* Title & bar */
        .ld-title {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            letter-spacing: -.02em;
            color: var(--ink);
            margin-bottom: 6px;
            opacity: 0;
            animation: ldFade .5s .3s ease both;
        }

        .ld-title em {
            font-style: italic;
            color: var(--accent);
        }

        .ld-sub {
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--faint);
            margin-bottom: 24px;
            opacity: 0;
            animation: ldFade .5s .45s ease both;
        }

        /* Segmented progress bar */
        .ld-segs {
            display: flex;
            gap: 5px;
            opacity: 0;
            animation: ldFade .3s .55s ease both;
        }

        .ld-seg {
            width: 22px;
            height: 3px;
            background: var(--border);
            border-radius: 3px;
            overflow: hidden;
            position: relative;
        }

        .ld-seg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, var(--accent), var(--acc2));
            border-radius: 3px;
            transform: scaleX(0);
            transform-origin: left;
        }

        .ld-seg:nth-child(1)::after {
            animation: ldSegFill .3s .55s ease forwards;
        }

        .ld-seg:nth-child(2)::after {
            animation: ldSegFill .3s .8s ease forwards;
        }

        .ld-seg:nth-child(3)::after {
            animation: ldSegFill .3s 1.05s ease forwards;
        }

        .ld-seg:nth-child(4)::after {
            animation: ldSegFill .3s 1.3s ease forwards;
        }

        .ld-seg:nth-child(5)::after {
            animation: ldSegFill .3s 1.55s ease forwards;
        }

        .ld-seg:nth-child(6)::after {
            animation: ldSegFill .3s 1.8s ease forwards;
        }

        @keyframes ldSegFill {
            to {
                transform: scaleX(1);
            }
        }

        @keyframes ldFade {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        /* ─── SCROLL PROGRESS ─── */
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

        /* ─── NAV ─── */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 56px;
            background: var(--nav-bg);
            backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid transparent;
            transition: border-color .3s, background .4s;
            animation: navIn .6s 1.2s cubic-bezier(.4, 0, .2, 1) both;
        }

        @keyframes navIn {
            from {
                opacity: 0;
                transform: translateY(-100%);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        nav.scrolled {
            border-bottom-color: var(--border);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            text-decoration: none;
        }

        .nav-icon {
            width: 36px;
            height: 36px;
            background: var(--accent);
            border-radius: 11px;
            display: grid;
            place-items: center;
            font-size: 17px;
            transition: transform .35s cubic-bezier(.34, 1.56, .64, 1), background .2s;
        }

        .nav-brand:hover .nav-icon {
            transform: rotate(-15deg) scale(1.1);
            background: var(--ink);
        }

        .nav-logo-text {
            font-family: 'Playfair Display', serif;
            font-size: 21px;
            letter-spacing: -.02em;
            color: var(--ink);
        }

        .nav-logo-text em {
            font-style: italic;
            color: var(--accent);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 32px;
            list-style: none;
        }

        .nav-links a {
            font-size: 14px;
            font-weight: 600;
            letter-spacing: .01em;
            color: var(--muted);
            text-decoration: none;
            transition: color .2s;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent);
            border-radius: 2px;
            transition: width .25s ease;
        }

        .nav-links a:hover {
            color: var(--ink);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-r {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dm-btn {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            border: 1.5px solid var(--border);
            background: var(--surface);
            display: grid;
            place-items: center;
            cursor: pointer;
            font-size: 17px;
            transition: all .2s;
        }

        .dm-btn:hover {
            border-color: var(--acc-brd);
            background: var(--acc-soft);
            transform: scale(1.08) rotate(15deg);
        }

        .nav-cta {
            height: 38px;
            padding: 0 22px;
            background: var(--accent);
            border: none;
            border-radius: 11px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .22s;
            position: relative;
            overflow: hidden;
            letter-spacing: .01em;
        }

        .nav-cta::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, .18);
            transform: translateX(-120%) skewX(-15deg);
            transition: transform .45s ease;
        }

        .nav-cta:hover::before {
            transform: translateX(140%) skewX(-15deg);
        }

        .nav-cta:hover {
            background: var(--ink);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(26, 61, 228, .3);
        }

        .nav-cta .arr {
            display: inline-block;
            transition: transform .2s;
        }

        .nav-cta:hover .arr {
            transform: translateX(5px);
        }

        /* ─── HERO ─── */
        .hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 140px 24px 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Noise texture overlay */
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
            opacity: .025;
            pointer-events: none;
            z-index: 0;
        }

        /* Animated dot grid */
        .hero-dots {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, var(--acc-brd) 1px, transparent 1px);
            background-size: 44px 44px;
            opacity: 0;
            animation: dotsReveal 1.5s .8s ease forwards;
            mask-image: radial-gradient(ellipse 90% 90% at 50% 50%, black 20%, transparent 75%);
            pointer-events: none;
            transition: opacity .4s;
        }

        @keyframes dotsReveal {
            to {
                opacity: .5;
            }
        }

        /* Radial colour blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
            opacity: 0;
            animation: blobIn 2s ease both;
        }

        @keyframes blobIn {
            from {
                opacity: 0;
                transform: scale(.7);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .blob-1 {
            width: 800px;
            height: 800px;
            background: rgba(26, 61, 228, .07);
            top: -300px;
            left: -300px;
            animation-delay: .4s;
            animation: blobIn 2s .4s ease both, floatA 12s ease-in-out infinite;
        }

        .blob-2 {
            width: 600px;
            height: 600px;
            background: rgba(79, 123, 251, .05);
            bottom: -200px;
            right: -200px;
            animation-delay: .6s;
            animation: blobIn 2s .6s ease both, floatB 14s ease-in-out infinite;
        }

        .blob-3 {
            width: 400px;
            height: 400px;
            background: rgba(26, 61, 228, .04);
            top: 40%;
            left: 60%;
            animation-delay: .8s;
            animation: blobIn 2s .8s ease both, floatC 10s ease-in-out infinite;
        }

        @keyframes floatA {

            0%,
            100% {
                transform: translate(0, 0)scale(1);
            }

            50% {
                transform: translate(60px, 40px)scale(1.04);
            }
        }

        @keyframes floatB {

            0%,
            100% {
                transform: translate(0, 0)scale(1);
            }

            50% {
                transform: translate(-50px, -60px)scale(1.03);
            }
        }

        @keyframes floatC {

            0%,
            100% {
                transform: translate(0, 0)scale(1);
            }

            50% {
                transform: translate(-30px, 40px)scale(1.05);
            }
        }

        .hero-inner {
            position: relative;
            z-index: 2;
            max-width: 860px;
        }

        /* Status pill */
        .hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 7px 18px;
            background: var(--surface);
            border: 1.5px solid var(--acc-brd);
            border-radius: 100px;
            font-family: 'Space Mono', monospace;
            font-size: 11.5px;
            letter-spacing: .06em;
            color: var(--accent);
            margin-bottom: 40px;
            box-shadow: var(--sh);
            opacity: 0;
            animation: pillDrop .6s 1.3s cubic-bezier(.34, 1.56, .64, 1) both;
        }

        @keyframes pillDrop {
            from {
                opacity: 0;
                transform: translateY(-14px)scale(.9);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .pill-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--ok);
            box-shadow: 0 0 0 3px var(--ok-brd);
            animation: livePulse 2.5s ease-in-out infinite;
        }

        @keyframes livePulse {

            0%,
            100% {
                box-shadow: 0 0 0 3px var(--ok-brd);
            }

            50% {
                box-shadow: 0 0 0 7px rgba(21, 122, 58, 0);
            }
        }

        /* Giant headline */
        .hero-h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(56px, 9vw, 110px);
            font-weight: 400;
            letter-spacing: -.04em;
            line-height: .96;
            color: var(--ink);
            margin-bottom: 28px;
            opacity: 0;
            animation: h1In 1s 1.45s cubic-bezier(.4, 0, .2, 1) both;
        }

        @keyframes h1In {
            from {
                opacity: 0;
                transform: translateY(36px)skewY(1.5deg);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .hero-h1 .word-em {
            font-style: italic;
            color: var(--accent);
            position: relative;
            display: inline-block;
        }

        /* Underline reveal */
        .hero-h1 .word-em::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--accent), var(--acc2));
            border-radius: 3px;
            opacity: .35;
            transform: scaleX(0);
            transform-origin: left;
            animation: ulReveal .7s 2.6s ease forwards;
        }

        @keyframes ulReveal {
            to {
                transform: scaleX(1);
            }
        }

        .hero-h1 .word-fade {
            display: block;
            color: var(--faint);
            animation: fadeLine .8s 2s ease both;
        }

        @keyframes fadeLine {
            from {
                opacity: 0;
                transform: translateX(-12px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        /* Sub */
        .hero-sub {
            font-size: clamp(16px, 2vw, 20px);
            font-weight: 400;
            color: var(--muted);
            line-height: 1.7;
            max-width: 540px;
            margin: 0 auto 48px;
            opacity: 0;
            animation: subIn .7s 1.7s ease both;
        }

        @keyframes subIn {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .hero-sub .hl {
            color: var(--ink);
            font-weight: 600;
            background: linear-gradient(120deg, var(--acc-soft) 0%, var(--acc-soft) 100%);
            background-repeat: no-repeat;
            background-position: 0 85%;
            background-size: 100% 35%;
            padding: 0 3px;
            border-radius: 3px;
        }

        /* CTAs */
        .hero-ctas {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
            opacity: 0;
            animation: subIn .7s 1.9s ease both;
        }

        .btn-main {
            height: 54px;
            padding: 0 32px;
            background: var(--accent);
            border: none;
            border-radius: 14px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .01em;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all .22s;
            position: relative;
            overflow: hidden;
        }

        .btn-main::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, .18);
            transform: translateX(-120%) skewX(-15deg);
            transition: transform .45s ease;
        }

        .btn-main:hover::before {
            transform: translateX(140%) skewX(-15deg);
        }

        .btn-main:hover {
            background: var(--ink);
            transform: translateY(-3px);
            box-shadow: 0 14px 36px rgba(26, 61, 228, .28);
        }

        .btn-main:active {
            transform: scale(.98);
        }

        .btn-main .arr {
            display: inline-block;
            transition: transform .2s;
        }

        .btn-main:hover .arr {
            transform: translateX(6px);
        }

        .btn-out {
            height: 54px;
            padding: 0 28px;
            background: transparent;
            border: 2px solid var(--border);
            border-radius: 14px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 600;
            color: var(--ink2);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            transition: all .22s;
        }

        .btn-out:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: var(--acc-soft);
            transform: translateY(-2px);
        }

        /* Stats strip */
        .stats-strip {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-top: 72px;
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--sh);
            opacity: 0;
            animation: subIn .7s 2.1s ease both;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            margin-top: 72px;
        }

        .stat {
            flex: 1;
            padding: 22px 20px;
            text-align: center;
            border-right: 1px solid var(--border);
            transition: background .2s;
            cursor: default;
        }

        .stat:last-child {
            border-right: none;
        }

        .stat:hover {
            background: var(--s2);
        }

        .stat-n {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            letter-spacing: -.03em;
            color: var(--accent);
            line-height: 1;
            margin-bottom: 4px;
            transition: transform .25s cubic-bezier(.34, 1.56, .64, 1);
        }

        .stat:hover .stat-n {
            transform: scale(1.08) translateY(-2px);
        }

        .stat-l {
            font-size: 11px;
            font-weight: 600;
            color: var(--faint);
            letter-spacing: .1em;
            text-transform: uppercase;
            font-family: 'Space Mono', monospace;
        }

        /* ─── MARQUEE STRIP ─── */
        .marquee-wrap {
            padding: 32px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            overflow: hidden;
            background: var(--surface);
            margin-bottom: 0;
        }

        .marquee-track {
            display: flex;
            gap: 0;
            animation: marquee 22s linear infinite;
            width: max-content;
        }

        .marquee-track:hover {
            animation-play-state: paused;
        }

        @keyframes marquee {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-50%);
            }
        }

        .marquee-item {
            white-space: nowrap;
            padding: 0 40px;
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--faint);
            display: flex;
            align-items: center;
            gap: 20px;
            border-right: 1px solid var(--border);
        }

        .marquee-item span {
            color: var(--accent);
            font-size: 18px;
        }

        /* ─── DEMO PREVIEW ─── */
        .demo-section {
            padding: 80px 24px 100px;
            max-width: 1060px;
            margin: 0 auto;
        }

        .section-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--accent);
            background: var(--acc-soft);
            border: 1.5px solid var(--acc-brd);
            padding: 5px 14px;
            border-radius: 100px;
            margin-bottom: 20px;
            transition: transform .2s, box-shadow .2s;
        }

        .section-tag:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(26, 61, 228, .14);
        }

        .section-h {
            font-family: 'Playfair Display', serif;
            font-size: clamp(34px, 5vw, 58px);
            font-weight: 400;
            letter-spacing: -.035em;
            line-height: 1.05;
            color: var(--ink);
            margin-bottom: 16px;
        }

        .section-h em {
            font-style: italic;
            color: var(--accent);
        }

        .section-p {
            font-size: 17px;
            font-weight: 400;
            color: var(--muted);
            line-height: 1.7;
            max-width: 500px;
            margin-bottom: 52px;
        }

        /* Browser mockup */
        .browser {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 22px;
            box-shadow: var(--sh3);
            overflow: hidden;
            opacity: 0;
            transform: translateY(56px) scale(.97);
            transition: opacity .9s cubic-bezier(.4, 0, .2, 1), transform .9s cubic-bezier(.4, 0, .2, 1);
        }

        .browser.vis {
            opacity: 1;
            transform: none;
        }

        .browser-bar {
            background: var(--s2);
            border-bottom: 1.5px solid var(--border);
            padding: 14px 22px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .b-dots {
            display: flex;
            gap: 7px;
        }

        .b-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .b-dot:nth-child(1) {
            background: #ff5f57;
        }

        .b-dot:nth-child(2) {
            background: #febc2e;
        }

        .b-dot:nth-child(3) {
            background: #28c840;
        }

        .browser:hover .b-dot {
            filter: brightness(1.2);
        }

        .b-url {
            flex: 1;
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 8px;
            padding: 6px 14px;
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            color: var(--faint);
            text-align: center;
        }

        .browser-body {
            padding: 32px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            min-height: 340px;
        }

        .b-left {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .b-textarea {
            flex: 1;
            background: var(--s2);
            border: 1.5px solid var(--border);
            border-radius: 14px;
            padding: 18px;
            font-family: 'Space Mono', monospace;
            font-size: 11.5px;
            line-height: 1.9;
            color: var(--muted);
            overflow: hidden;
        }

        .b-cursor {
            display: inline-block;
            width: 2px;
            height: 14px;
            background: var(--accent);
            vertical-align: middle;
            animation: cur 1s step-end infinite;
            margin-left: 1px;
        }

        @keyframes cur {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }
        }

        .b-btn {
            height: 44px;
            background: var(--accent);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .b-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .22), transparent);
            animation: btnShine 3.5s 2s ease-in-out infinite;
        }

        @keyframes btnShine {
            0% {
                left: -100%;
            }

            50%,
            100% {
                left: 160%;
            }
        }

        .b-right {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .b-score-box {
            background: var(--s2);
            border: 1.5px solid var(--border);
            border-radius: 14px;
            padding: 18px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .b-gauge {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            flex-shrink: 0;
            background: conic-gradient(var(--ok) calc(var(--p, 72)*1%), var(--border) 0);
            display: grid;
            place-items: center;
            position: relative;
        }

        .b-gauge::before {
            content: '';
            position: absolute;
            inset: 9px;
            background: var(--s2);
            border-radius: 50%;
        }

        .b-gauge-n {
            position: relative;
            font-family: 'Playfair Display', serif;
            font-size: 14px;
            color: var(--ink);
            z-index: 1;
        }

        .b-verdict {
            font-family: 'Playfair Display', serif;
            font-size: 16px;
            margin-bottom: 3px;
        }

        .b-sub {
            font-size: 11.5px;
            color: var(--muted);
            line-height: 1.5;
        }

        .b-findings {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .b-finding {
            background: var(--s2);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 12px;
            color: var(--ink2);
            line-height: 1.55;
            opacity: 0;
            transform: translateX(14px);
            transition: background .2s;
        }

        .b-finding:hover {
            background: var(--acc-soft);
            border-color: var(--acc-brd);
        }

        .browser.vis .b-finding:nth-child(1) {
            animation: findIn .5s .5s ease forwards;
        }

        .browser.vis .b-finding:nth-child(2) {
            animation: findIn .5s .7s ease forwards;
        }

        .browser.vis .b-finding:nth-child(3) {
            animation: findIn .5s .9s ease forwards;
        }

        @keyframes findIn {
            to {
                opacity: 1;
                transform: none;
            }
        }

        .b-fdot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: 3px;
            animation: dotPop 2s ease-in-out infinite;
        }

        @keyframes dotPop {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.5);
            }
        }

        /* ─── HOW IT WORKS ─── */
        .how-section {
            padding: 100px 24px;
            max-width: 1060px;
            margin: 0 auto;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .step {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 20px;
            padding: 36px 30px;
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(32px);
            transition: opacity .65s ease, transform .65s ease, box-shadow .25s, border-color .25s;
            cursor: default;
        }

        .step.vis {
            opacity: 1;
            transform: none;
        }

        .step:nth-child(2) {
            transition-delay: .12s;
        }

        .step:nth-child(3) {
            transition-delay: .24s;
        }

        /* Accent top bar */
        .step::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--acc2));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .35s ease;
        }

        .step:hover {
            box-shadow: var(--sh2);
            border-color: var(--acc-brd);
        }

        .step:hover::before {
            transform: scaleX(1);
        }

        /* Mouse-track glow */
        .step::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at var(--mx, 50%) var(--my, 50%), rgba(26, 61, 228, .05) 0%, transparent 60%);
            opacity: 0;
            transition: opacity .3s;
            pointer-events: none;
        }

        .step:hover::after {
            opacity: 1;
        }

        .step-num {
            font-family: 'Playfair Display', serif;
            font-size: 54px;
            letter-spacing: -.04em;
            color: var(--acc-brd);
            line-height: 1;
            margin-bottom: 20px;
            transition: color .3s, transform .3s;
        }

        .step:hover .step-num {
            color: var(--accent);
            transform: scale(1.04) translateX(3px);
        }

        .step-ico {
            width: 46px;
            height: 46px;
            background: var(--acc-soft);
            border: 1.5px solid var(--acc-brd);
            border-radius: 13px;
            display: grid;
            place-items: center;
            font-size: 21px;
            margin-bottom: 18px;
            transition: all .3s cubic-bezier(.34, 1.56, .64, 1);
        }

        .step:hover .step-ico {
            background: var(--accent);
            transform: scale(1.1) rotate(6deg);
        }

        .step-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 9px;
            letter-spacing: -.01em;
        }

        .step-desc {
            font-size: 14px;
            font-weight: 400;
            color: var(--muted);
            line-height: 1.7;
        }

        /* ─── FEATURES ─── */
        .feat-section {
            padding: 0 24px 100px;
            max-width: 1060px;
            margin: 0 auto;
        }

        .feat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .feat {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 20px;
            padding: 34px;
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity .6s ease, transform .6s ease, box-shadow .25s, border-color .25s;
            cursor: default;
        }

        .feat.vis {
            opacity: 1;
            transform: none;
        }

        .feat:nth-child(2) {
            transition-delay: .08s;
        }

        .feat:nth-child(3) {
            transition-delay: .16s;
        }

        .feat:nth-child(4) {
            transition-delay: .24s;
        }

        .feat::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--acc2));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .35s ease;
        }

        .feat::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(26, 61, 228, .04), transparent);
            transition: left .5s ease;
        }

        .feat:hover {
            box-shadow: var(--sh2);
            border-color: var(--acc-brd);
        }

        .feat:hover::before {
            transform: scaleX(1);
        }

        .feat:hover::after {
            left: 150%;
        }

        .feat-ico {
            width: 46px;
            height: 46px;
            background: var(--acc-soft);
            border: 1.5px solid var(--acc-brd);
            border-radius: 13px;
            display: grid;
            place-items: center;
            font-size: 21px;
            margin-bottom: 18px;
            transition: all .3s cubic-bezier(.34, 1.56, .64, 1);
        }

        .feat:hover .feat-ico {
            background: var(--accent);
            transform: scale(1.1) rotate(-6deg);
        }

        .feat-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 9px;
            letter-spacing: -.01em;
        }

        .feat-desc {
            font-size: 14px;
            font-weight: 400;
            color: var(--muted);
            line-height: 1.7;
        }

        .feat-tag {
            display: inline-block;
            margin-top: 16px;
            font-family: 'Space Mono', monospace;
            font-size: 10.5px;
            font-weight: 500;
            padding: 4px 11px;
            background: var(--acc-soft);
            border: 1.5px solid var(--acc-brd);
            color: var(--accent);
            border-radius: 100px;
            transition: all .2s;
        }

        .feat:hover .feat-tag {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        /* ─── CTA BLOCK ─── */
        .cta-section {
            padding: 0 24px 120px;
            max-width: 1060px;
            margin: 0 auto;
        }

        .cta-block {
            background: var(--ink);
            border-radius: 28px;
            padding: 90px 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(32px);
            transition: opacity .8s ease, transform .8s ease;
        }

        .cta-block.vis {
            opacity: 1;
            transform: none;
        }

        /* Moving mesh inside CTA */
        .cta-mesh {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 70% at 20% 50%, rgba(26, 61, 228, .3) 0%, transparent 55%),
                radial-gradient(ellipse 50% 60% at 80% 50%, rgba(79, 123, 251, .2) 0%, transparent 55%);
            animation: ctaMesh 8s ease-in-out infinite alternate;
            pointer-events: none;
        }

        @keyframes ctaMesh {
            from {
                opacity: .7;
            }

            to {
                opacity: 1;
            }
        }

        .cta-grid {
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(255, 255, 255, .04) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, .04) 1px, transparent 1px);
            background-size: 44px 44px;
            animation: gridDrift 10s linear infinite;
            pointer-events: none;
        }

        @keyframes gridDrift {
            from {
                background-position: 0 0;
            }

            to {
                background-position: 44px 44px;
            }
        }

        .cta-orb {
            position: absolute;
            width: 350px;
            height: 350px;
            background: rgba(255, 255, 255, .05);
            border-radius: 50%;
            filter: blur(80px);
            animation: ctaOrb 7s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes ctaOrb {

            0%,
            100% {
                transform: translate(-60px, -30px);
            }

            50% {
                transform: translate(80px, 50px);
            }
        }

        .cta-inner {
            position: relative;
            z-index: 2;
        }

        .cta-eyebrow {
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .45);
            margin-bottom: 20px;
        }

        .cta-h {
            font-family: 'Playfair Display', serif;
            font-size: clamp(32px, 5.5vw, 60px);
            font-weight: 400;
            letter-spacing: -.035em;
            line-height: 1.08;
            color: #fff;
            margin-bottom: 16px;
        }

        .cta-h em {
            font-style: italic;
            color: #93c5fd;
        }

        .cta-p {
            font-size: 17px;
            font-weight: 400;
            color: rgba(255, 255, 255, .55);
            line-height: 1.7;
            max-width: 420px;
            margin: 0 auto 36px;
        }

        .btn-white {
            height: 54px;
            padding: 0 36px;
            background: #fff;
            border: none;
            border-radius: 14px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .01em;
            color: var(--accent);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all .22s;
            position: relative;
            overflow: hidden;
        }

        .btn-white::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(26, 61, 228, .07), transparent);
            opacity: 0;
            transition: opacity .2s;
        }

        .btn-white:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, .28);
        }

        .btn-white:hover::before {
            opacity: 1;
        }

        .btn-white .arr {
            display: inline-block;
            transition: transform .2s;
        }

        .btn-white:hover .arr {
            transform: translateX(6px);
        }

        /* ─── FOOTER ─── */
        footer {
            border-top: 1.5px solid var(--border);
            padding: 30px 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            background: var(--surface);
        }

        .ft-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .ft-icon {
            width: 30px;
            height: 30px;
            background: var(--accent);
            border-radius: 9px;
            display: grid;
            place-items: center;
            font-size: 14px;
            transition: transform .3s cubic-bezier(.34, 1.56, .64, 1);
        }

        .ft-brand:hover .ft-icon {
            transform: rotate(-12deg) scale(1.1);
        }

        .ft-name {
            font-family: 'Playfair Display', serif;
            font-size: 17px;
            color: var(--ink);
        }

        .ft-name em {
            font-style: italic;
            color: var(--accent);
        }

        .ft-copy {
            font-family: 'Space Mono', monospace;
            font-size: 11.5px;
            color: var(--faint);
        }

        /* ─── BACK TO TOP ─── */
        #btt {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 44px;
            height: 44px;
            background: var(--accent);
            border: none;
            border-radius: 13px;
            display: grid;
            place-items: center;
            cursor: pointer;
            font-size: 18px;
            color: #fff;
            opacity: 0;
            transform: translateY(12px) scale(.9);
            transition: opacity .3s, transform .3s, background .2s;
            z-index: 50;
            box-shadow: 0 4px 18px rgba(26, 61, 228, .35);
        }

        #btt.show {
            opacity: 1;
            transform: none;
        }

        #btt:hover {
            background: var(--ink);
            transform: translateY(-3px) scale(1.05);
        }

        /* ─── REVEAL ─── */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity .75s ease, transform .75s ease;
        }

        .reveal.vis {
            opacity: 1;
            transform: none;
        }

        @media(max-width:768px) {
            nav {
                padding: 0 20px;
            }

            .nav-links {
                display: none;
            }

            .hero-h1 {
                font-size: 52px;
            }

            .steps-grid {
                grid-template-columns: 1fr;
            }

            .feat-grid {
                grid-template-columns: 1fr;
            }

            .browser-body {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .b-right {
                display: none;
            }

            footer {
                padding: 24px 20px;
                flex-direction: column;
                align-items: flex-start;
            }

            .cta-block {
                padding: 52px 28px;
            }

            .stats-strip {
                flex-direction: column;
            }

            .stat {
                border-right: none;
                border-bottom: 1px solid var(--border);
            }

            .stat:last-child {
                border-bottom: none;
            }
        }

        @media(max-width:480px) {
            .hero-h1 {
                font-size: 42px;
            }

            .stat-n {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

    <div id="loader">
        <div class="ld-rings">
            <div class="ld-ring ld-ring-1"></div>
            <div class="ld-ring ld-ring-2"></div>
            <div class="ld-ring ld-ring-3"></div>
            <div class="ld-orbit">
                <div class="ld-orb-dot"></div>
            </div>
            <div class="ld-center">🔍</div>
        </div>
        <div class="ld-title">Plagia<em>Scope</em></div>
        <div class="ld-sub">Research Integrity Tool</div>
        <div class="ld-segs">
            <div class="ld-seg"></div>
            <div class="ld-seg"></div>
            <div class="ld-seg"></div>
            <div class="ld-seg"></div>
            <div class="ld-seg"></div>
            <div class="ld-seg"></div>
        </div>
    </div>

    <div id="spb"></div>
    <div id="btt" onclick="window.scrollTo({top:0,behavior:'smooth'})">↑</div>

    <nav id="mainNav">
        <a class="nav-brand" href="#">
            <div class="nav-icon">🔍</div>
            <div class="nav-logo-text">Plagia<em>Scope</em></div>
        </a>
        <ul class="nav-links">
            <li><a href="#how">How it works</a></li>
            <li><a href="#features">Features</a></li>
        </ul>
        <div class="nav-r">
            <button class="dm-btn" id="dmBtn">🌙</button>
            <a class="nav-cta" href="/chat">Open Checker <span class="arr">→</span></a>

        </div>
    </nav>

    <!-- ══ HERO ══ -->
    <section class="hero">
        <div class="hero-dots"></div>
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>

        <div class="hero-inner">
            <div class="hero-pill">
                <span class="pill-dot"></span>
                Powered by Winston AI · English Research
            </div>

            <h1 class="hero-h1">
                Write with <span class="word-em">confidence.</span>
                <span class="word-fade">Submit with integrity.</span>
            </h1>

            <p class="hero-sub">
                PlagiaScope scans your research against <span class="hl">400 billion sources</span> to detect plagiarism, missing citations, and matching content — in seconds.
            </p>

            <div class="hero-ctas">
                <a class="btn-main" href="chat">Start Checking Free <span class="arr">→</span></a>
                <a class="btn-out" href="#how">See how it works</a>
            </div>

            <div class="stats-strip">
                <div class="stat">
                    <div class="stat-n" data-target="400" data-suffix="B+">0</div>
                    <div class="stat-l">Sources scanned</div>
                </div>
                <div class="stat">
                    <div class="stat-n" data-target="47" data-suffix="">0</div>
                    <div class="stat-l">Languages</div>
                </div>
                <div class="stat">
                    <div class="stat-n" data-target="30" data-suffix="s">0</div>
                    <div class="stat-l">Avg scan time</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ MARQUEE ══ -->
    <div class="marquee-wrap">
        <div class="marquee-track">
            <!-- doubled for seamless loop -->
            <div class="marquee-item"><span>✦</span> Verbatim Detection</div>
            <div class="marquee-item"><span>✦</span> Paraphrase Analysis</div>
            <div class="marquee-item"><span>✦</span> Citation Integrity</div>
            <div class="marquee-item"><span>✦</span> Source Attribution</div>
            <div class="marquee-item"><span>✦</span> 47 Languages</div>
            <div class="marquee-item"><span>✦</span> File Upload Support</div>
            <div class="marquee-item"><span>✦</span> PDF · DOC · DOCX</div>
            <div class="marquee-item"><span>✦</span> AI-Powered Scanning</div>
            <div class="marquee-item"><span>✦</span> Verbatim Detection</div>
            <div class="marquee-item"><span>✦</span> Paraphrase Analysis</div>
            <div class="marquee-item"><span>✦</span> Citation Integrity</div>
            <div class="marquee-item"><span>✦</span> Source Attribution</div>
            <div class="marquee-item"><span>✦</span> 47 Languages</div>
            <div class="marquee-item"><span>✦</span> File Upload Support</div>
            <div class="marquee-item"><span>✦</span> PDF · DOC · DOCX</div>
            <div class="marquee-item"><span>✦</span> AI-Powered Scanning</div>
        </div>
    </div>

    <!-- ══ DEMO WINDOW ══ -->
    <section class="demo-section">
        <div class="reveal">
            <div class="section-tag">✦ Live Preview</div>
            <h2 class="section-h">See it in <em>action</em></h2>
            <p class="section-p">Paste your text, hit analyze, and get a full integrity report in under 30 seconds.</p>
        </div>

        <div class="browser" id="browser">
            <div class="browser-bar">
                <div class="b-dots">
                    <div class="b-dot"></div>
                    <div class="b-dot"></div>
                    <div class="b-dot"></div>
                </div>
                <div class="b-url">localhost/plagiarism/chat.php</div>
            </div>
            <div class="browser-body">
                <div class="b-left">
                    <div class="b-textarea">
                        <span id="typed"></span><span class="b-cursor"></span>
                    </div>
                    <div class="b-btn">🔍 Analyze for Plagiarism</div>
                </div>
                <div class="b-right">
                    <div class="b-score-box">
                        <div class="b-gauge" style="--p:72">
                            <div class="b-gauge-n">72%</div>
                        </div>
                        <div>
                            <div class="b-verdict">Mostly Original</div>
                            <div class="b-sub">2 sources matched.<br>Review highlighted areas.</div>
                        </div>
                    </div>
                    <div class="b-findings">
                        <div class="b-finding">
                            <div class="b-fdot" style="background:#dc2626"></div>
                            <div><strong>Missing Citation</strong> — Unattributed claim about climate data from a known source.</div>
                        </div>
                        <div class="b-finding">
                            <div class="b-fdot" style="background:#d97706"></div>
                            <div><strong>Verbatim Match</strong> — Sequence matches published research without quotation.</div>
                        </div>
                        <div class="b-finding">
                            <div class="b-fdot" style="background:#2563eb"></div>
                            <div><strong>Similar Phrasing</strong> — Close rewording of existing literature without reference.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ HOW IT WORKS ══ -->
    <section class="how-section" id="how">
        <div class="reveal">
            <div class="section-tag">✦ Process</div>
            <h2 class="section-h">How it <em>works</em></h2>
            <p class="section-p">Three steps from paste to results — no account, no sign-up required.</p>
        </div>

        <div class="steps-grid">
            <div class="step">
                <div class="step-num">01</div>
                <div class="step-ico">📋</div>
                <div class="step-title">Paste text or upload a file</div>
                <div class="step-desc">Paste your English research paper or upload PDF, DOC, or DOCX. Works with anything from a paragraph to a full paper.</div>
            </div>
            <div class="step">
                <div class="step-num">02</div>
                <div class="step-ico">🔍</div>
                <div class="step-title">Winston AI scans 400B sources</div>
                <div class="step-desc">Your text is compared against billions of webpages, academic publications, and databases to find any matching content.</div>
            </div>
            <div class="step">
                <div class="step-num">03</div>
                <div class="step-ico">📊</div>
                <div class="step-title">Get your full report</div>
                <div class="step-desc">Receive an originality score, annotated text with highlighted passages, and a ranked list of matching sources — in seconds.</div>
            </div>
        </div>
    </section>

    <!-- ══ FEATURES ══ -->
    <section class="feat-section" id="features">
        <div class="reveal">
            <div class="section-tag">✦ Capabilities</div>
            <h2 class="section-h">Everything you need to <em>submit</em> with confidence</h2>
            <p class="section-p">Built for students, researchers, and academics who take integrity seriously.</p>
        </div>

        <div class="feat-grid">
            <div class="feat">
                <div class="feat-ico">🔍</div>
                <div class="feat-title">Verbatim Detection</div>
                <div class="feat-desc">Identifies exact text matches and direct copy-paste patterns against billions of published sources and academic databases.</div>
                <span class="feat-tag">Core check</span>
            </div>
            <div class="feat">
                <div class="feat-ico">📄</div>
                <div class="feat-title">File Upload Support</div>
                <div class="feat-desc">Upload PDF, DOC, or DOCX files directly. Winston AI extracts and scans the full text — no copy-pasting needed for long documents.</div>
                <span class="feat-tag">PDF · DOC · DOCX</span>
            </div>
            <div class="feat">
                <div class="feat-ico">📚</div>
                <div class="feat-title">Source Attribution</div>
                <div class="feat-desc">Every flagged passage is linked to its matching source — title, URL, match percentage, and the exact plagiarized sequences.</div>
                <span class="feat-tag">Full traceability</span>
            </div>
            <div class="feat">
                <div class="feat-ico">🌐</div>
                <div class="feat-title">47 Languages</div>
                <div class="feat-desc">Supports English and 46 other languages with automatic detection. Optimized for English academic writing with country-specific scanning.</div>
                <span class="feat-tag">Auto-detect</span>
            </div>
        </div>
    </section>

    <!-- ══ CTA ══ -->
    <section class="cta-section">
        <div class="cta-block" id="ctaBlock">
            <div class="cta-mesh"></div>
            <div class="cta-grid"></div>
            <div class="cta-orb"></div>
            <div class="cta-inner">
                <div class="cta-eyebrow">Ready when you are</div>
                <h2 class="cta-h">Check your research.<br><em>Right now.</em></h2>
                <p class="cta-p">No sign-up. No waiting. Paste your text or upload your file and get results in seconds.</p>
                <a class="btn-white" href="chat">Open PlagiaScope <span class="arr">→</span></a>
            </div>
        </div>
    </section>

    <footer>
        <a class="ft-brand" href="#">
            <div class="ft-icon">🔍</div>
            <div class="ft-name">Plagia<em>Scope</em></div>
        </a>
        <div class="ft-copy">© 2026 PlagiaScope · Powered by Winston AI</div>
    </footer>

    <script>
        // ── Loader — dismiss after 3.5s (long enough to see the full animation) ──
        function dismissLoader() {
            document.getElementById('loader').classList.add('out');
        }
        setTimeout(dismissLoader, 3500);
        if (document.readyState === 'complete') {
            setTimeout(dismissLoader, 3500);
        } else {
            window.addEventListener('load', () => setTimeout(dismissLoader, 3500));
        }

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

        // ── Scroll ──
        const mainNav = document.getElementById('mainNav');
        const spb = document.getElementById('spb');
        const bttBtn = document.getElementById('btt');
        window.addEventListener('scroll', () => {
            const sy = window.scrollY;
            const tot = document.documentElement.scrollHeight - window.innerHeight;
            mainNav.classList.toggle('scrolled', sy > 20);
            spb.style.width = (sy / tot * 100) + '%';
            bttBtn.classList.toggle('show', sy > 400);
        });

        // ── Intersection observer ──
        const obs = new IntersectionObserver(entries => entries.forEach(e => {
            if (e.isIntersecting) e.target.classList.add('vis');
        }), {
            threshold: .1,
            rootMargin: '0px 0px -40px 0px'
        });
        document.querySelectorAll('.reveal,.step,.feat,.browser,#ctaBlock').forEach(el => obs.observe(el));

        // ── Mouse-track on steps ──
        document.querySelectorAll('.step').forEach(s => {
            s.addEventListener('mousemove', e => {
                const r = s.getBoundingClientRect();
                s.style.setProperty('--mx', ((e.clientX - r.left) / r.width * 100).toFixed(1) + '%');
                s.style.setProperty('--my', ((e.clientY - r.top) / r.height * 100).toFixed(1) + '%');
            });
        });

        // ── Typewriter ──
        const txt = 'The greenhouse effect is a natural process\nby which certain gases in the atmosphere\ntrap heat from the sun. Scientists have\nextensively studied this phenomenon...';
        let ci = 0;
        const tel = document.getElementById('typed');

        function type() {
            if (ci < txt.length) {
                tel.innerHTML += txt[ci] === '\n' ? '<br>' : txt[ci];
                ci++;
                setTimeout(type, ci < 60 ? 32 : 15);
            }
        }
        setTimeout(type, 2200);

        // ── Counters ──
        function animN(el, target, suffix) {
            let cur = 0;
            const step = target / (1200 / 16);
            const t = setInterval(() => {
                cur = Math.min(cur + step, target);
                el.textContent = Math.round(cur) + suffix;
                if (cur >= target) clearInterval(t);
            }, 16);
        }
        const sObs = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (!e.isIntersecting) return;
                document.querySelectorAll('[data-target]').forEach(el => {
                    animN(el, parseInt(el.getAttribute('data-target')), el.getAttribute('data-suffix') || '');
                });
                sObs.disconnect();
            });
        }, {
            threshold: .5
        });
        const strip = document.querySelector('.stats-strip');
        if (strip) sObs.observe(strip);
    </script>
</body>

</html>