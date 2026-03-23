// ─────────────────────────────────────────────
//  PlagiaScope — Proxy Server
//  Relays requests to Winston AI to bypass CORS
//
//  SETUP:
//    1. Make sure Node.js is installed
//    2. Run:  node server.js
//    3. Open: http://localhost:3000
// ─────────────────────────────────────────────

const http  = require('http');
const https = require('https');
const fs    = require('fs');
const path  = require('path');
const url   = require('url');

const PORT = 3000;

// ── MIME types ──
const MIME = {
  '.html': 'text/html',
  '.js':   'application/javascript',
  '.css':  'text/css',
  '.json': 'application/json',
  '.png':  'image/png',
  '.ico':  'image/x-icon',
};

// ── CORS headers ──
const CORS = {
  'Access-Control-Allow-Origin':  '*',
  'Access-Control-Allow-Methods': 'GET, POST, OPTIONS',
  'Access-Control-Allow-Headers': 'Content-Type, Authorization',
};

const server = http.createServer((req, res) => {
  const parsed  = url.parse(req.url, true);
  const reqPath = parsed.pathname;

  // ── Handle preflight ──
  if (req.method === 'OPTIONS') {
    res.writeHead(204, CORS);
    res.end();
    return;
  }

  // ── Proxy: POST /api/plagiarism → Winston AI ──
  if (reqPath === '/api/plagiarism' && req.method === 'POST') {
    let body = '';

    req.on('data', chunk => { body += chunk; });
    req.on('end', () => {
      let payload;
      try {
        payload = JSON.parse(body);
      } catch (e) {
        res.writeHead(400, { 'Content-Type': 'application/json', ...CORS });
        res.end(JSON.stringify({ error: 'Invalid JSON body' }));
        return;
      }

      const apiKey = payload._apiKey;
      delete payload._apiKey; // strip key from forwarded body

      if (!apiKey) {
        res.writeHead(401, { 'Content-Type': 'application/json', ...CORS });
        res.end(JSON.stringify({ error: 'No API key provided' }));
        return;
      }

      const postData = JSON.stringify(payload);

      const options = {
        hostname: 'api.gowinston.ai',
        path:     '/v2/plagiarism',
        method:   'POST',
        headers: {
          'Authorization': `Bearer ${apiKey}`,
          'Content-Type':  'application/json',
          'Content-Length': Buffer.byteLength(postData),
        },
      };

      const proxyReq = https.request(options, (proxyRes) => {
        let data = '';
        proxyRes.on('data', chunk => { data += chunk; });
        proxyRes.on('end', () => {
          res.writeHead(proxyRes.statusCode, {
            'Content-Type': 'application/json',
            ...CORS,
          });
          res.end(data);
        });
      });

      proxyReq.on('error', (err) => {
        console.error('Winston AI proxy error:', err.message);
        res.writeHead(502, { 'Content-Type': 'application/json', ...CORS });
        res.end(JSON.stringify({ error: 'Failed to reach Winston AI: ' + err.message }));
      });

      proxyReq.write(postData);
      proxyReq.end();
    });

    return;
  }

  // ── Serve static files ──
  let filePath = reqPath === '/' ? '/index.html' : reqPath;
  filePath = path.join(__dirname, filePath);

  const ext  = path.extname(filePath);
  const mime = MIME[ext] || 'application/octet-stream';

  fs.readFile(filePath, (err, data) => {
    if (err) {
      if (err.code === 'ENOENT') {
        res.writeHead(404, { 'Content-Type': 'text/plain' });
        res.end('404 Not Found: ' + reqPath);
      } else {
        res.writeHead(500, { 'Content-Type': 'text/plain' });
        res.end('500 Server Error');
      }
      return;
    }
    res.writeHead(200, { 'Content-Type': mime });
    res.end(data);
  });
});

server.listen(PORT, () => {
  console.log('');
  console.log('  ✦ PlagiaScope server running');
  console.log(`  → Open: http://localhost:${PORT}`);
  console.log('');
});