#!/usr/bin/env node
/**
 * Fail CI on high/critical npm advisories, with an explicit allowlist for
 * known unpatched ecosystem issues.
 */
import { execFileSync } from 'node:child_process';

const ALLOWED = new Set([
  // picomatch ReDoS via single-branch extglobs — no release newer than 4.0.7 yet.
  // https://github.com/advisories/GHSA-fc5q-39xc-vjpf
  'GHSA-FC5Q-39XC-VJPF',
]);

function advisoryId(via) {
  if (typeof via?.url === 'string') {
    const match = via.url.match(/GHSA-[a-z0-9-]+/i);
    if (match) {
      return match[0].toUpperCase();
    }
  }

  return null;
}

let report;
try {
  execFileSync('npm', ['audit', '--json'], {
    encoding: 'utf8',
    stdio: ['ignore', 'pipe', 'pipe'],
  });
  report = { vulnerabilities: {} };
} catch (error) {
  const stdout = typeof error.stdout === 'string' ? error.stdout : '';
  report = JSON.parse(stdout || '{"vulnerabilities":{}}');
}

const blockers = [];

for (const [name, entry] of Object.entries(report.vulnerabilities || {})) {
  for (const via of entry.via || []) {
    if (typeof via !== 'object' || via === null) {
      continue;
    }

    const severity = String(via.severity || '').toLowerCase();
    if (severity !== 'high' && severity !== 'critical') {
      continue;
    }

    const id = advisoryId(via);
    if (id && ALLOWED.has(id)) {
      continue;
    }

    blockers.push({
      name,
      id: id || 'unknown',
      title: via.title || 'Untitled advisory',
      severity,
    });
  }
}

if (blockers.length > 0) {
  console.error('npm audit found blocking high/critical advisories:');
  for (const item of blockers) {
    console.error(`- [${item.severity}] ${item.name}: ${item.title} (${item.id})`);
  }
  process.exit(1);
}

const allowedNote = ALLOWED.size > 0
  ? ` (allowlisted: ${[...ALLOWED].join(', ')})`
  : '';
console.log(`npm audit: no blocking high/critical advisories${allowedNote}.`);
