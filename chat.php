<?php
require_once __DIR__ . '/app/session.php';
start_app_session();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PlagiaScope | AI</title>
  <script src="config.js"></script>
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
  <link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho+B1:wght@400;500;600;700;800&family=Shippori+Mincho:wght@400;500;600;700;800&family=Noto+Sans+JP:wght@300;400;500;600;700&family=Zen+Kaku+Gothic+New:wght@300;400;500;700;900&display=swap" rel="stylesheet">
  <link rel="icon" href="favicon.svg" type="image/svg+xml">
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

      --font-display: 'Shippori Mincho B1', 'Shippori Mincho', serif;
      --font-body: 'Noto Sans JP', sans-serif;
      --font-mono: 'Zen Kaku Gothic New', monospace;
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
      font-family: var(--font-body);
      min-height: 100vh;
      -webkit-font-smoothing: antialiased;
      transition: background .4s, color .4s;
    }

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
      font-family: var(--font-display);
      font-size: 19px;
      font-weight: 700;
      letter-spacing: .04em;
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
      font-family: var(--font-mono);
      font-size: 13px;
      font-weight: 600;
      letter-spacing: .04em;
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

    /* ─── AUTH & USER STYLES ─── */
    .nav-link {
      height: 36px;
      padding: 0 14px;
      background: transparent;
      border: 1.5px solid var(--border);
      border-radius: 10px;
      font-family: var(--font-mono);
      font-size: 12px;
      font-weight: 600;
      color: var(--muted);
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all .2s;
    }

    .nav-link:hover {
      color: var(--accent);
      border-color: var(--acc-brd);
      background: var(--acc-soft);
      transform: translateY(-2px);
    }

    .login-btn {
      height: 36px;
      padding: 0 16px;
      background: var(--accent);
      border: none;
      border-radius: 10px;
      font-family: var(--font-mono);
      font-size: 12px;
      font-weight: 600;
      color: #fff;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all .2s;
    }

    .login-btn:hover {
      background: var(--ink);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(26, 61, 228, .25);
    }

    .user-menu {
      display: flex;
      align-items: center;
      gap: 10px;
      position: relative;
    }

    .user-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--border);
      cursor: pointer;
      transition: all .2s;
    }

    .user-avatar:hover {
      border-color: var(--accent);
      transform: scale(1.05);
    }

    .user-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      margin-top: 8px;
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 12px;
      padding: 8px 0;
      min-width: 180px;
      box-shadow: var(--sh2);
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px);
      transition: all .2s;
      z-index: 100;
    }

    .user-menu:hover .user-dropdown,
    .user-dropdown.show {
      opacity: 1;
      visibility: visible;
      transform: none;
    }

    .dropdown-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 16px;
      font-family: var(--font-body);
      font-size: 13px;
      color: var(--ink);
      text-decoration: none;
      transition: background .15s;
      cursor: pointer;
      border: none;
      background: none;
      width: 100%;
      text-align: left;
    }

    .dropdown-item:hover {
      background: var(--s2);
    }

    .dropdown-divider {
      height: 1px;
      background: var(--border);
      margin: 8px 0;
    }

    .user-name {
      font-family: var(--font-mono);
      font-size: 12px;
      font-weight: 600;
      color: var(--ink);
      max-width: 120px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    /* ─── HISTORY PANEL ─── */
    .history-toggle {
      height: 36px;
      padding: 0 14px;
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      font-family: var(--font-mono);
      font-size: 12px;
      font-weight: 600;
      color: var(--muted);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: all .2s;
    }

    .history-toggle:hover {
      border-color: var(--accent);
      color: var(--accent);
    }

    .history-panel {
      position: fixed;
      top: 60px;
      right: -400px;
      width: 360px;
      bottom: 0;
      background: var(--surface);
      border-left: 1.5px solid var(--border);
      box-shadow: -4px 0 24px rgba(0, 0, 0, .15);
      transition: right .3s ease;
      z-index: 90;
      display: flex;
      flex-direction: column;
    }

    .history-panel.open {
      right: 0;
    }

    .history-header {
      padding: 20px 24px;
      border-bottom: 1.5px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .history-title {
      font-family: var(--font-display);
      font-size: 18px;
      font-weight: 700;
      color: var(--ink);
    }

    .history-close {
      width: 32px;
      height: 32px;
      border: 1.5px solid var(--border);
      background: var(--surface);
      border-radius: 8px;
      cursor: pointer;
      display: grid;
      place-items: center;
      font-size: 16px;
      transition: all .2s;
    }

    .history-close:hover {
      border-color: var(--danger);
      color: var(--danger);
    }

    .history-list {
      flex: 1;
      overflow-y: auto;
      padding: 16px;
    }

    .history-empty {
      text-align: center;
      padding: 40px 20px;
      color: var(--faint);
      font-family: var(--font-body);
      font-size: 14px;
    }

    .history-item {
      background: var(--s2);
      border: 1.5px solid var(--border);
      border-radius: 12px;
      padding: 14px 16px;
      margin-bottom: 12px;
      cursor: pointer;
      transition: all .2s;
    }

    .history-item:hover {
      border-color: var(--accent);
      transform: translateX(-4px);
    }

    .history-preview {
      font-family: var(--font-body);
      font-size: 13px;
      color: var(--ink2);
      line-height: 1.5;
      margin-bottom: 10px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .history-meta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-family: var(--font-mono);
      font-size: 11px;
      color: var(--faint);
    }

    .history-score {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 3px 8px;
      background: var(--ok-bg);
      border: 1px solid var(--ok-brd);
      border-radius: 100px;
      color: var(--ok);
      font-weight: 600;
    }

    .history-score.high-risk {
      background: var(--dbg);
      border-color: var(--dbrd);
      color: var(--danger);
    }

    .history-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .3);
      opacity: 0;
      visibility: hidden;
      transition: all .3s;
      z-index: 85;
    }

    .history-overlay.show {
      opacity: 1;
      visibility: visible;
    }

    .page {
      max-width: 880px;
      margin: 0 auto;
      padding: 44px 24px 80px;
    }

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
      font-family: var(--font-mono);
      font-size: 11px;
      letter-spacing: .1em;
      color: var(--accent);
      margin-bottom: 14px;
      animation: fadeUp .5s .2s ease both;
    }

    .pg-title {
      font-family: var(--font-display);
      font-size: clamp(28px, 5vw, 46px);
      font-weight: 700;
      letter-spacing: .02em;
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
      font-family: var(--font-body);
      font-size: 15px;
      font-weight: 400;
      color: var(--muted);
      max-width: 400px;
      margin: 0 auto;
      line-height: 1.75;
      animation: fadeUp .6s .4s ease both;
    }

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
      font-family: var(--font-mono);
      font-size: 13px;
      font-weight: 600;
      letter-spacing: .04em;
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
      font-family: var(--font-mono);
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
      font-family: var(--font-display);
      font-size: 14px;
      font-weight: 700;
      letter-spacing: .02em;
      color: var(--ink);
    }

    .card-sub {
      font-family: var(--font-mono);
      font-size: 11px;
      color: var(--muted);
      margin-top: 2px;
      letter-spacing: .04em;
    }

    textarea {
      width: 100%;
      background: transparent;
      border: none;
      resize: none;
      color: var(--ink2);
      font-family: var(--font-mono);
      font-size: 13px;
      line-height: 1.9;
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
      font-family: var(--font-mono);
      font-size: 11px;
      color: var(--faint);
      background: var(--s2);
      letter-spacing: .04em;
    }

    .wn {
      font-weight: 700;
      transition: color .2s;
    }

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
      font-family: var(--font-display);
      font-size: 15px;
      font-weight: 700;
      color: var(--ink);
      margin-bottom: 6px;
      letter-spacing: .02em;
    }

    .drop-sub {
      font-family: var(--font-body);
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
      font-family: var(--font-mono);
      font-size: 11px;
      font-weight: 500;
      padding: 4px 11px;
      border-radius: 100px;
      border: 1.5px solid var(--acc-brd);
      background: var(--acc-soft);
      color: var(--accent);
      transition: all .2s;
      letter-spacing: .06em;
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
      font-family: var(--font-display);
      font-size: 14px;
      font-weight: 700;
      color: var(--ink);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .fprev-size {
      font-family: var(--font-mono);
      font-size: 11px;
      color: var(--muted);
      margin-top: 2px;
      letter-spacing: .04em;
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

    .err {
      display: none;
      padding: 13px 16px;
      background: var(--dbg);
      border: 1.5px solid var(--dbrd);
      border-radius: var(--rs);
      font-family: var(--font-body);
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

    .captcha-wrap {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--r);
      box-shadow: var(--sh);
      padding: 16px 18px;
      margin-bottom: 14px;
      animation: fadeUp .5s .58s ease both;
    }

    .captcha-title {
      font-family: var(--font-display);
      font-size: 13px;
      font-weight: 700;
      color: var(--ink);
      margin-bottom: 6px;
      letter-spacing: .02em;
    }

    .captcha-sub {
      font-family: var(--font-body);
      font-size: 12px;
      color: var(--muted);
      margin-bottom: 12px;
    }

    .run-btn {
      width: 100%;
      height: 56px;
      background: var(--accent);
      border: none;
      border-radius: var(--r);
      font-family: var(--font-display);
      font-size: 20px;
      font-weight: 700;
      letter-spacing: .04em;
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
      font-family: var(--font-mono);
      font-size: 12px;
      color: var(--muted);
      letter-spacing: .06em;
    }

    .ld-text strong {
      display: block;
      font-family: var(--font-display);
      font-size: 15px;
      font-weight: 700;
      color: var(--ink2);
      margin-bottom: 5px;
      animation: phPop .4s ease;
      letter-spacing: .04em;
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

    .results {
      display: none;
    }

    .results.on {
      display: block;
      animation: fadeUp .5s ease;
    }

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
      font-family: var(--font-display);
      font-size: 30px;
      font-weight: 700;
      line-height: 1;
      letter-spacing: .02em;
    }

    .g-lbl {
      font-family: var(--font-mono);
      font-size: 9px;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: .12em;
      margin-top: 4px;
    }

    .o-verdict {
      font-family: var(--font-display);
      font-size: 28px;
      font-weight: 700;
      letter-spacing: .02em;
      margin-bottom: 8px;
      line-height: 1.15;
    }

    .o-desc {
      font-family: var(--font-body);
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
      font-family: var(--font-mono);
      font-size: 11px;
      padding: 4px 11px;
      border-radius: 100px;
      border: 1.5px solid var(--acc-brd);
      background: var(--acc-soft);
      color: var(--accent);
      animation: pPop .4s cubic-bezier(.34, 1.56, .64, 1) both;
      transition: transform .2s;
      letter-spacing: .05em;
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
      font-family: var(--font-display);
      font-size: 40px;
      font-weight: 700;
      letter-spacing: .02em;
      line-height: 1;
      margin-bottom: 6px;
    }

    .tile-l {
      font-family: var(--font-mono);
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .1em;
      color: var(--muted);
    }

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
      font-family: var(--font-mono);
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: var(--muted);
      background: var(--s2);
    }

    .sec-r {
      font-family: var(--font-mono);
      font-size: 11px;
      color: var(--faint);
      text-transform: none;
      letter-spacing: .04em;
      font-weight: 400;
    }

    .anno-body {
      padding: 22px;
      font-family: var(--font-mono);
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
      font-family: var(--font-mono);
      font-size: 10.5px;
      color: var(--muted);
      letter-spacing: .04em;
    }

    .leg-d {
      width: 10px;
      height: 10px;
      border-radius: 3px;
    }

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
      font-family: var(--font-mono);
      font-size: 11px;
      color: var(--faint);
      min-width: 26px;
      padding-top: 2px;
      font-weight: 500;
      letter-spacing: .04em;
    }

    .src-title {
      font-family: var(--font-display);
      font-size: 14px;
      font-weight: 700;
      color: var(--ink);
      margin-bottom: 3px;
      line-height: 1.4;
      letter-spacing: .02em;
    }

    .src-url {
      font-family: var(--font-mono);
      font-size: 11px;
      color: var(--accent);
      text-decoration: none;
      display: block;
      margin-bottom: 6px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 520px;
      letter-spacing: .02em;
    }

    .src-url:hover {
      text-decoration: underline;
    }

    .src-desc {
      font-family: var(--font-body);
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
      font-family: var(--font-mono);
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
      font-family: var(--font-display);
      font-size: 24px;
      font-weight: 700;
      letter-spacing: .02em;
      color: var(--danger);
      line-height: 1;
    }

    .src-pct-l {
      font-family: var(--font-mono);
      font-size: 9px;
      color: var(--faint);
      text-transform: uppercase;
      letter-spacing: .1em;
      margin-top: 3px;
    }

    .src-w {
      font-family: var(--font-mono);
      font-size: 10px;
      color: var(--faint);
      margin-top: 4px;
    }

    .cite-tag {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-family: var(--font-mono);
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
      font-family: var(--font-body);
      font-size: 13px;
      color: var(--muted);
      flex-wrap: wrap;
      gap: 8px;
      animation: fadeUp .4s ease both;
    }

    .cred-used {
      font-family: var(--font-mono);
      font-size: 11px;
      padding: 3px 11px;
      background: var(--wbg);
      border: 1.5px solid var(--wbrd);
      color: var(--warn);
      border-radius: 100px;
      letter-spacing: .05em;
    }

    .cred-left {
      font-family: var(--font-mono);
      font-size: 11px;
      padding: 3px 11px;
      background: var(--okbg);
      border: 1.5px solid var(--okbrd);
      color: var(--ok);
      border-radius: 100px;
      letter-spacing: .05em;
    }

    .empty {
      padding: 44px 24px;
      text-align: center;
      font-family: var(--font-body);
      font-size: 14px;
      color: var(--muted);
    }

    .reset-btn {
      width: 100%;
      height: 46px;
      background: transparent;
      border: 1.5px solid var(--border);
      border-radius: var(--r);
      font-family: var(--font-mono);
      font-size: 13px;
      font-weight: 600;
      letter-spacing: .06em;
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
    <a class="brand" href="<?php echo htmlspecialchars(app_path('/')); ?>">
      <div class="brand-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8" />
          <path d="m21 21-4.35-4.35" />
        </svg></div>
      <div class="brand-name">Plagia<em>Scope</em></div>
    </a>
    <div class="top-r">
      <button class="dm-btn" id="dmBtn" style="display: grid; place-items: center;">
        <svg id="dmSun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="5" />
          <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
        </svg>
        <svg id="dmMoon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
          <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
        </svg>
      </button>

      <?php if (!empty($_SESSION['is_logged_in'])): ?>
        <!-- Logged in user -->
        <button class="history-toggle" id="historyToggle" title="View scan history">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
            <circle cx="12" cy="12" r="10" />
            <polyline points="12 6 12 12 16 14" />
          </svg> History
        </button>
        <div class="user-menu" id="userMenu">
          <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
          <img src="<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? 'https://www.gravatar.com/avatar/?d=mp'); ?>"
            alt="Avatar" class="user-avatar" id="userAvatar">
          <div class="user-dropdown">
            <div class="dropdown-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg> <?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>
            </div>
            <div class="dropdown-divider"></div>
            <a href="<?php echo htmlspecialchars(app_path('/')); ?>" class="dropdown-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                <polyline points="9 22 9 12 15 12 15 22" />
              </svg> Home
            </a>
            <a href="<?php echo htmlspecialchars(app_path('user')); ?>" class="dropdown-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;">
                <path d="M3 3v18h18" />
                <path d="M18 17V9" />
                <path d="M13 17V5" />
                <path d="M8 17v-3" />
              </svg> My Account
            </a>
            <a href="<?php echo htmlspecialchars(app_path('auth/logout')); ?>" class="dropdown-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <polyline points="16 17 21 12 16 7" />
                <line x1="21" y1="12" x2="9" y2="12" />
              </svg> Logout
            </a>
          </div>
        </div>
      <?php else: ?>
        <!-- Guest user -->
        <a href="<?php echo htmlspecialchars(app_path('/')); ?>" class="nav-link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            <polyline points="9 22 9 12 15 12 15 22" />
          </svg> Home
        </a>
        <a href="<?php echo htmlspecialchars(app_path('auth/google')); ?>" class="login-btn">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg> Sign in with Google
        </a>
      <?php endif; ?>

    </div>
  </nav>

  <!-- History Panel (only for logged-in users) -->
  <?php if (!empty($_SESSION['is_logged_in'])): ?>
    <div class="history-overlay" id="historyOverlay"></div>
    <div class="history-panel" id="historyPanel">
      <div class="history-header">
        <div class="history-title"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;">
            <circle cx="12" cy="12" r="10" />
            <polyline points="12 6 12 12 16 14" />
          </svg> Scan History</div>
        <button class="history-close" id="historyClose"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 6 6 18M6 6l12 12" />
          </svg></button>
      </div>
      <div class="history-list" id="historyList">
        <div class="history-empty">Loading your scan history...</div>
      </div>
    </div>
  <?php endif; ?>

  <div class="page">
    <div class="pg-header">
      <div class="pg-eyebrow"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" stroke="none" style="vertical-align: middle; margin-right: 4px;">
          <polygon points="12 2 15 9 22 9 16 14 18 21 12 17 6 21 8 14 2 9 9 9" />
        </svg> Winston AI · English Research</div>
      <h1 class="pg-title">Research <em>Integrity</em> Checker</h1>
      <p class="pg-sub">Paste your text or upload a file — get your plagiarism report in seconds.</p>
    </div>

    <div id="inputSection">
      <div class="tabs">
        <button class="tab on" id="tabText" onclick="switchMode('text')">
          Paste Text <span class="tab-badge">TXT</span>
        </button>
        <button class="tab" id="tabFile" onclick="switchMode('file')">
          Upload File <span class="tab-badge">PDF · DOC · DOCX</span>
        </button>
      </div>

      <div class="text-panel" id="textPanel">
        <div class="card">
          <div class="card-head">
            <div class="card-ico"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                <path d="M18.375 2.625a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4Z" />
              </svg></div>
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

      <div class="captcha-wrap">
        <div class="captcha-title">Human verification</div>
        <div class="captcha-sub">Please complete the security check before running the scan.</div>
        <div class="cf-turnstile" data-sitekey="0x4AAAAAACu7HA_zSWn5iEok"></div>
      </div>

      <button class="run-btn" id="runBtn" onclick="analyze()"> Analyze for Plagiarism</button>
    </div>

    <div class="loading" id="loadWrap">
      <div class="scan-wrap">
        <div class="s-arc s-arc-1"></div>
        <div class="s-arc s-arc-2"></div>
        <div class="s-arc s-arc-3"></div>
        <div class="s-orbit">
          <div class="s-dot"></div>
        </div>
        <div class="s-center"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.35-4.35" />
          </svg></div>
      </div>
      <div class="ld-text">
        <strong id="ldPhase">Connecting to Winston AI…</strong>
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

    <div class="results" id="resultsSection">
      <div class="origin-card">
        <div class="gauge-wrap">
          <svg class="g-svg" width="124" height="124" viewBox="0 0 124 124">
            <circle class="g-bg" cx="62" cy="62" r="49" />
            <circle class="g-fill" id="gFill" cx="62" cy="62" r="49" />
          </svg>
          <div class="g-center">
            <div class="g-num" id="gNum">0%</div>
            <div class="g-lbl">Plagiarism</div>
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
    const TURNSTILE_SITE_KEY = '0x4AAAAAACu7HA_zSWn5iEok';
    const proxyUrl = <?php echo json_encode(app_path('proxy')); ?>;
    const historyUrl = <?php echo json_encode(app_path('api/history')); ?>;

    const htmlEl = document.documentElement;
    const dmBtn = document.getElementById('dmBtn');
    const saved = localStorage.getItem('ps-theme');
    if (saved) {
      htmlEl.setAttribute('data-theme', saved);
      document.getElementById('dmSun').style.display = saved === 'dark' ? 'block' : 'none';
      document.getElementById('dmMoon').style.display = saved === 'dark' ? 'none' : 'block';
    }
    dmBtn.addEventListener('click', () => {
      const next = htmlEl.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      htmlEl.setAttribute('data-theme', next);
      document.getElementById('dmSun').style.display = next === 'dark' ? 'block' : 'none';
      document.getElementById('dmMoon').style.display = next === 'dark' ? 'none' : 'block';
      localStorage.setItem('ps-theme', next);
    });

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
      document.getElementById('fprevIco').innerHTML = ext === 'pdf' ? '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z\"/><polyline points=\"14 2 14 8 20 8\"/><path d=\"M9 15v-2"/><path d=\"M12 15v-6"/><path d=\"M15 15v-4"/></svg>' : '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z\"/><polyline points=\"14 2 14 8 20 8\"/></svg>';
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
      document.getElementById('charHint').textContent = t.length >= 100 ? 'Ready to scan' : '100 chars minimum';
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

    function getTurnstileToken() {
      const tokenField = document.querySelector('[name="cf-turnstile-response"]');
      return tokenField ? tokenField.value.trim() : '';
    }

    function resetTurnstileWidget() {
      if (window.turnstile) {
        try {
          window.turnstile.reset();
        } catch (e) {}
      }
    }

    const phases = [
      'Connecting to Winston AI…',
      'Uploading to scan engine…',
      'Comparing text sequences…',
      'Identifying matching passages…',
      'Scoring originality…',
      'Building your report…'
    ];
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
      const turnstileToken = getTurnstileToken();
      if (!turnstileToken) {
        showErr('Please complete the human verification first.');
        return;
      }
      const cfg = getConfig();
      let opts;

      if (mode === 'file') {
        if (!selFile) {
          showErr('Please select a file to scan.');
          return;
        }
        const fd = new FormData();
        fd.append('language', cfg.language || 'en');
        fd.append('country', cfg.country || 'us');
        fd.append('file', selFile, selFile.name);
        fd.append('cf-turnstile-response', turnstileToken);
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
            text,
            language: cfg.language || 'en',
            country: cfg.country || 'us',
            'cf-turnstile-response': turnstileToken
          })
        };
      }

      document.getElementById('runBtn').disabled = true;
      document.getElementById('inputSection').style.display = 'none';
      document.getElementById('loadWrap').classList.add('on');
      document.getElementById('resultsSection').classList.remove('on');
      startLoading();

      try {
        const res = await fetch(proxyUrl, opts);
        const data = await res.json();
        if (!res.ok) {
          // Check if limit reached and show premium modal
          if (res.status === 429 && data.show_premium) {
            stopLoading();
            document.getElementById('loadWrap').classList.remove('on');
            document.getElementById('inputSection').style.display = 'block';
            document.getElementById('runBtn').disabled = false;
            resetTurnstileWidget();
            showPremiumModal(data.scans_today || 0);
            return;
          }
          throw new Error(data?.message || data?.error || `API error ${res.status}`);
        }
        stopLoading();
        const txt = data.text || (mode === 'text' ? document.getElementById('mainText').value.trim() : '');
        renderResults(data, txt);
      } catch (err) {
        stopLoading();
        document.getElementById('loadWrap').classList.remove('on');
        document.getElementById('inputSection').style.display = 'block';
        document.getElementById('runBtn').disabled = false;
        resetTurnstileWidget();
        showErr('Error: ' + (err.message || 'Unexpected error.'));
      }
    }

    function renderResults(data, origText) {
      document.getElementById('runBtn').disabled = false;
      resetTurnstileWidget();
      document.getElementById('loadWrap').classList.remove('on');
      document.getElementById('resultsSection').classList.add('on');

      const result = data.result || {};
      const sources = data.sources || [];
      const indexes = data.indexes || [];
      const plagScore = result.score ?? 0;
      const strokeColor = plagScore <= 15 ? '#157a3a' : plagScore <= 30 ? '#c47a00' : '#d42020';

      const gFill = document.getElementById('gFill');
      gFill.style.stroke = strokeColor;
      setTimeout(() => {
        gFill.style.strokeDashoffset = 310 - (plagScore / 100) * 310;
      }, 80);

      let cur = 0;
      const nEl = document.getElementById('gNum');
      nEl.style.color = strokeColor;
      const timer = setInterval(() => {
        cur = Math.min(cur + 1.5, plagScore);
        nEl.textContent = Math.round(cur) + '%';
        if (cur >= plagScore) clearInterval(timer);
      }, 16);

      const verdict = plagScore <= 15 ? 'Highly Original' : plagScore <= 30 ? 'Mostly Original' : plagScore <= 50 ? 'Partially Plagiarized' : 'High Plagiarism Risk';
      const desc = plagScore <= 15 ? 'Your text appears largely original.' : plagScore <= 30 ? 'Some matching content found. Review highlighted passages.' : plagScore <= 50 ? 'Significant matches detected. Revise and cite sources.' : 'Large portion matches existing sources. Extensive revision required.';

      document.getElementById('verdict').textContent = verdict;
      document.getElementById('odesc').textContent = desc;

      const pEl = document.getElementById('mpills');
      pEl.innerHTML = '';
      [`${result.sourceCounts ?? sources.length} sources matched`, `${result.textWordCounts ?? '—'} words scanned`, `${result.totalPlagiarismWords ?? 0} plagiarized words`].forEach(l => {
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
      ].forEach((tile, i) => {
        const d = document.createElement('div');
        d.className = 's-tile';
        d.style.animationDelay = (i * .08) + 's';
        d.innerHTML = `<div class="tile-v" style="color:${tile.color}">${tile.val}</div><div class="tile-l">${tile.label}</div>`;
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
          ptr = 0;
        sorted.forEach(idx => {
          if (idx.startIndex > ptr) html += esc(origText.slice(ptr, idx.startIndex));
          html += `<mark class="plag" title="Plagiarized passage">${esc(origText.slice(idx.startIndex, idx.endIndex))}</mark>`;
          ptr = idx.endIndex;
        });
        if (ptr < origText.length) html += esc(origText.slice(ptr));
        aEl.innerHTML = html;
        document.getElementById('annoSub').textContent = `${indexes.length} passage${indexes.length !== 1 ? 's' : ''} flagged`;
      } else if (origText) {
        aEl.textContent = origText;
        document.getElementById('annoSub').textContent = 'No plagiarism detected';
      } else {
        document.getElementById('annoCard').style.display = 'none';
      }

      const sEl = document.getElementById('srcList');
      sEl.innerHTML = '';
      document.getElementById('srcCount').textContent = sources.length ? `${sources.length} source${sources.length !== 1 ? 's' : ''}` : '';

      if (!sources.length) {
        sEl.innerHTML = '<div class="empty">✓ No matching sources found — your text appears original.</div>';
      } else {
        [...sources].sort((a, b) => (b.score ?? 0) - (a.score ?? 0)).forEach((src, i) => {
          const item = document.createElement('div');
          item.className = 'src-item';
          item.style.animationDelay = (i * .06) + 's';
          const sv = typeof src.score === 'number' ? src.score.toFixed(1) + '%' : '—';
          const ct = src.citation ? `<span class="cite-tag">✓ cited</span>` : '';
          const sq = (src.plagiarismFound || []).slice(0, 3).map(p => `<div class="src-seq">"${esc(p.sequence)}"</div>`).join('');
          item.innerHTML = `
            <div class="src-rank">${String(i + 1).padStart(2, '0')}</div>
            <div>
              <div class="src-title">${esc(src.title || 'Untitled source')}${ct}</div>
              <a class="src-url" href="${esc(src.url || '#')}" target="_blank" rel="noopener">${esc(src.url || '')}</a>
              ${src.description ? `<div class="src-desc">${esc(src.description)}</div>` : ''}
              ${sq ? `<div class="src-seqs">${sq}</div>` : ''}
            </div>
            <div class="src-score">
              <div class="src-pct">${sv}</div>
              <div class="src-pct-l">match</div>
              <div class="src-w">${src.plagiarismWords ?? 0} words</div>
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
      document.getElementById('runBtn').disabled = false;
      resetTurnstileWidget();
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }

    /* ─── HISTORY PANEL ─── */
    <?php if (!empty($_SESSION['is_logged_in'])): ?>
        (function() {
          const historyToggle = document.getElementById('historyToggle');
          const historyPanel = document.getElementById('historyPanel');
          const historyClose = document.getElementById('historyClose');
          const historyOverlay = document.getElementById('historyOverlay');
          const historyList = document.getElementById('historyList');
          let historyLoaded = false;

          function openHistory() {
            historyPanel.classList.add('open');
            historyOverlay.classList.add('show');
            document.body.style.overflow = 'hidden';
            if (!historyLoaded) {
              loadHistory();
            }
          }

          function closeHistory() {
            historyPanel.classList.remove('open');
            historyOverlay.classList.remove('show');
            document.body.style.overflow = '';
          }

          historyToggle.addEventListener('click', openHistory);
          historyClose.addEventListener('click', closeHistory);
          historyOverlay.addEventListener('click', closeHistory);

          // Close on escape key
          document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeHistory();
          });

          async function loadHistory() {
            try {
              const response = await fetch(historyUrl);
              const data = await response.json();

              if (!data.success) {
                historyList.innerHTML = `<div class="history-empty">${data.error || 'Failed to load history'}</div>`;
                return;
              }

              if (data.scans.length === 0) {
                historyList.innerHTML = `<div class="history-empty">No scans yet. Start checking your research!</div>`;
                return;
              }

              historyList.innerHTML = data.scans.map(scan => {
                const date = new Date(scan.created_at).toLocaleDateString('en-US', {
                  month: 'short',
                  day: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit'
                });
                const score = Math.round(scan.plagiarism_score || 0);
                const scoreClass = score > 50 ? 'high-risk' : '';
                const preview = esc(scan.text_preview || 'No preview');

                return `
              <div class="history-item" data-id="${scan.id}">
                <div class="history-preview">${preview}</div>
                <div class="history-meta">
                  <span>${date}</span>
                  <span class="history-score ${scoreClass}">${score}% match</span>
                </div>
              </div>
            `;
              }).join('');

              // Add click handlers to history items
              document.querySelectorAll('.history-item').forEach((item, index) => {
                item.style.cursor = 'pointer';
                item.addEventListener('click', () => {
                  const scan = data.scans[index];
                  showScanDetailModal(scan);
                });
              });

              historyLoaded = true;
            } catch (error) {
              console.error('Failed to load history:', error);
              historyList.innerHTML = `<div class="history-empty">Failed to load history. Please try again.</div>`;
            }
          }
        })();
    <?php endif; ?>

    // Toast notification helper
    function showToast(message) {
      const existing = document.querySelector('.toast-msg');
      if (existing) existing.remove();

      const toast = document.createElement('div');
      toast.className = 'toast-msg';
      toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--ink);
        color: #fff;
        padding: 12px 24px;
        border-radius: 10px;
        font-family: var(--font-body);
        font-size: 14px;
        z-index: 1000;
        animation: fadeUp .3s ease;
      `;
      toast.textContent = message;
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
    }

    // Scan Detail Modal for history sidebar
    function showScanDetailModal(scan) {
      const existing = document.getElementById('scanDetailModal');
      if (existing) existing.remove();

      const result = scan.result || {};
      const sources = result.sources || [];
      const score = Math.round(scan.plagiarism_score || 0);
      const fullText = result.text ? esc(result.text.substring(0, 2000)) + (result.text.length > 2000 ? '...' : '') : '';

      let sourcesHtml = '';
      if (sources.length === 0) {
        sourcesHtml = '<p style="color: var(--muted); text-align: center; padding: 16px;">No matching sources found.</p>';
      } else {
        sourcesHtml = sources.slice(0, 5).map((src, i) => `
          <div style="display: flex; gap: 16px; padding: 16px; border: 1.5px solid var(--border); border-radius: 10px; margin-bottom: 12px; background: var(--surface); transition: all .2s;">
            <div style="width: 40px; height: 40px; border-radius: 8px; background: var(--s2); display: grid; place-items: center; font-family: var(--font-mono); font-size: 14px; font-weight: 600; color: var(--accent); flex-shrink: 0;">${String(i + 1).padStart(2, '0')}</div>
            <div style="flex: 1; min-width: 0;">
              <div style="font-size: 15px; font-weight: 600; color: var(--ink); margin-bottom: 4px; line-height: 1.3;">${esc(src.title || 'Untitled')}</div>
              <a href="${esc(src.url || '#')}" target="_blank" rel="noopener" style="font-family: var(--font-mono); font-size: 12px; color: var(--accent); word-break: break-all;">${esc(src.url || '')}</a>
              <div style="font-family: var(--font-mono); font-size: 11px; color: var(--muted); margin-top: 6px;">Match: ${src.score ? src.score.toFixed(1) : '0'}% | ${src.plagiarismWords || 0} words</div>
            </div>
          </div>
        `).join('');
      }

      const modal = document.createElement('div');
      modal.id = 'scanDetailModal';
      modal.style.cssText = `
        position: fixed;
        inset: 0;
        background: rgba(14, 12, 9, .75);
        backdrop-filter: blur(8px);
        z-index: 3000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        animation: fadeIn .3s ease;
      `;

      modal.innerHTML = `
        <div style="
          background: var(--surface);
          border: 1.5px solid var(--border);
          border-radius: 16px;
          box-shadow: var(--sh2), 0 25px 50px -12px rgba(14, 12, 9, .25);
          width: 100%;
          max-width: 700px;
          max-height: 85vh;
          overflow: hidden;
          display: flex;
          flex-direction: column;
          animation: slideUp .4s ease;
        ">
          <div style="padding: 20px 28px; border-bottom: 1.5px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: var(--s2);">
            <h3 style="font-family: var(--font-display); font-size: 20px; font-weight: 600; color: var(--ink); margin: 0;">Scan Details</h3>
            <button onclick="this.closest('#scanDetailModal').remove()" style="width: 40px; height: 40px; border-radius: 10px; border: 1.5px solid var(--border); background: var(--surface); cursor: pointer; display: grid; place-items: center; transition: all .2s;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div style="padding: 28px; overflow-y: auto;">
            <div style="display: flex; gap: 28px; align-items: flex-start; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1.5px solid var(--border);">
              <div style="text-align: center; padding: 24px 32px; border-radius: 14px; min-width: 140px; flex-shrink: 0; ${score > 50 ? 'background: var(--dbg); border: 2px solid var(--dbrd);' : score > 20 ? 'background: var(--wbg); border: 2px solid var(--wbrd);' : 'background: var(--okbg); border: 2px solid var(--okbrd);'}">
                <span style="font-family: var(--font-display); font-size: 44px; font-weight: 700; color: ${score > 50 ? 'var(--danger)' : score > 20 ? 'var(--warn)' : 'var(--ok)'}; display: block; line-height: 1;">${score}%</span>
                <span style="font-family: var(--font-mono); font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: .1em; margin-top: 8px; display: block;">Plagiarism Score</span>
              </div>
              <div style="flex: 1; display: grid; gap: 10px;">
                <p style="font-size: 15px; color: var(--ink); margin: 0;"><strong style="color: var(--muted); font-weight: 500; display: inline-block; min-width: 80px;">Date:</strong> ${new Date(scan.created_at).toLocaleString()}</p>
                <p style="font-size: 15px; color: var(--ink); margin: 0;"><strong style="color: var(--muted); font-weight: 500; display: inline-block; min-width: 80px;">Sources:</strong> ${sources.length}</p>
                ${scan.file_name ? `<p style="font-size: 15px; color: var(--ink); margin: 0;"><strong style="color: var(--muted); font-weight: 500; display: inline-block; min-width: 80px;">File:</strong> ${esc(scan.file_name)}</p>` : ''}
              </div>
            </div>
            
            ${fullText ? `
            <div style="margin-bottom: 24px;">
              <h4 style="font-family: var(--font-display); font-size: 15px; font-weight: 600; margin-bottom: 12px; color: var(--ink);">Original Text</h4>
              <div style="background: var(--s2); border: 1.5px solid var(--border); border-radius: 10px; padding: 16px; font-family: var(--font-body); font-size: 14px; line-height: 1.6; color: var(--ink2); max-height: 200px; overflow-y: auto; white-space: pre-wrap; word-break: break-word;">${fullText}</div>
            </div>
            ` : ''}
            
            <div>
              <h4 style="font-family: var(--font-display); font-size: 15px; font-weight: 600; margin-bottom: 14px; color: var(--ink);">Matching Sources (${sources.length})</h4>
              ${sourcesHtml}
            </div>
          </div>
        </div>
      `;

      modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.remove();
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') modal.remove();
      }, {
        once: true
      });

      document.body.appendChild(modal);
    }

    // Premium Modal
    function showPremiumModal(scansToday) {
      const existing = document.getElementById('premiumModal');
      if (existing) existing.remove();

      const modal = document.createElement('div');
      modal.id = 'premiumModal';
      modal.style.cssText = `
        position: fixed;
        inset: 0;
        background: rgba(14, 12, 9, .7);
        backdrop-filter: blur(6px);
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        animation: fadeIn .3s ease;
      `;

      modal.innerHTML = `
        <div style="
          background: var(--surface);
          border: 2px solid var(--accent);
          border-radius: var(--r);
          box-shadow: var(--sh2), 0 0 60px rgba(26, 61, 228, .15);
          width: 100%;
          max-width: 420px;
          padding: 32px;
          text-align: center;
          animation: slideUp .4s ease;
        ">
          <div style="font-size: 48px; margin-bottom: 16px;">⭐</div>
          <h3 style="
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 12px;
          ">Daily Limit Reached</h3>
          <p style="
            font-family: var(--font-body);
            font-size: 14px;
            color: var(--muted);
            line-height: 1.6;
            margin-bottom: 24px;
          ">
            You've used <strong>${scansToday} of 10</strong> free scans today.<br>
            Upgrade to Premium for unlimited scans and advanced features.
          </p>
          <div style="display: flex; gap: 12px; flex-direction: column;">
            <button onclick="alert('Premium upgrade coming soon!')" style="
              padding: 14px 24px;
              background: var(--accent);
              color: #fff;
              border: none;
              border-radius: var(--rx);
              font-family: var(--font-mono);
              font-size: 14px;
              font-weight: 600;
              cursor: pointer;
              transition: all .2s;
            " onmouseover="this.style.background='var(--ink)'; this.style.transform='translateY(-2px)';" 
            onmouseout="this.style.background='var(--accent)'; this.style.transform='none';">
              Upgrade to Premium
            </button>
            <button onclick="this.closest('#premiumModal').remove()" style="
              padding: 12px 24px;
              background: transparent;
              color: var(--muted);
              border: 1.5px solid var(--border);
              border-radius: var(--rx);
              font-family: var(--font-mono);
              font-size: 13px;
              font-weight: 500;
              cursor: pointer;
              transition: all .2s;
            " onmouseover="this.style.borderColor='var(--border2)'; this.style.color='var(--ink)';"
            onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--muted)';">
              Maybe Later
            </button>
          </div>
        </div>
      `;

      modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.remove();
      });

      document.body.appendChild(modal);
    }
  </script>
</body>

</html>