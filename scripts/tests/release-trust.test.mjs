import test from 'node:test';
import assert from 'node:assert/strict';
import { generateKeyPairSync, sign } from 'node:crypto';
import { validateRun, validateMetadata, verifyArchive } from '../release-trust.mjs';

const repository = 'owner/cms';
const run = {
  id: 123, repository: { full_name: repository }, head_repository: { full_name: repository },
  head_branch: 'main', event: 'push', path: '.github/workflows/ci.yml',
  status: 'completed', conclusion: 'success', head_sha: 'a'.repeat(40),
};
test('accepts successful trusted main CI', () => validateRun(run, repository));
test('rejects PRs, forks, other workflows, and failed or incomplete runs', () => {
  for (const change of [
    { event: 'pull_request' }, { head_branch: 'feature' }, { conclusion: 'failure' },
    { status: 'in_progress' }, { path: '.github/workflows/other.yml' },
    { head_repository: { full_name: 'fork/cms' } }, { head_sha: 'invalid' },
  ]) assert.throws(() => validateRun({ ...run, ...change }, repository));
});
test('binds release to exact repository, commit, run, and runtime', () => {
  const meta = { repository, commit: run.head_sha, run_id: '123', runtime: 'laravel-php83' };
  validateMetadata(meta, run, repository);
  for (const field of Object.keys(meta)) {
    assert.throws(() => validateMetadata({ ...meta, [field]: 'wrong' }, run, repository));
  }
});
test('rejects altered archives and signatures from another key', () => {
  const { privateKey, publicKey } = generateKeyPairSync('rsa', { modulusLength: 2048 });
  const bytes = Buffer.from('release archive');
  const signature = sign('sha256', bytes, privateKey);
  verifyArchive(bytes, signature, publicKey);
  assert.throws(() => verifyArchive(Buffer.from('tampered'), signature, publicKey));
  const other = generateKeyPairSync('rsa', { modulusLength: 2048 });
  assert.throws(() => verifyArchive(bytes, signature, other.publicKey));
});
