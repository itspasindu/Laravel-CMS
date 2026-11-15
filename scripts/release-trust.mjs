import { readFileSync, writeFileSync } from 'node:fs';
import { sign, verify } from 'node:crypto';
import { pathToFileURL } from 'node:url';

export function validateRun(run, repository) {
  if (!repository || run.repository?.full_name !== repository ||
      run.head_repository?.full_name !== repository || run.head_branch !== 'main' ||
      !['push', 'workflow_dispatch'].includes(run.event) ||
      run.path !== '.github/workflows/ci.yml' || run.status !== 'completed' ||
      run.conclusion !== 'success' || !/^[a-f0-9]{40}$/.test(run.head_sha)) {
    throw new Error('Release must come from a successful main-branch CI run in this repository');
  }
}

export function validateMetadata(meta, run, repository) {
  validateRun(run, repository);
  if (meta.repository !== repository || meta.commit !== run.head_sha ||
      meta.run_id !== String(run.id) || meta.runtime !== 'laravel-php83') {
    throw new Error('Release metadata does not match source CI run');
  }
}

export function verifyArchive(bytes, signature, key) {
  if (!verify('sha256', bytes, key, signature)) throw new Error('Invalid release signature');
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
  const [command, file, runFile] = process.argv.slice(2);
  const json = (path) => JSON.parse(readFileSync(path, 'utf8'));
  if (command === 'sign') {
    writeFileSync(`${file}.sig`, sign('sha256', readFileSync(file), process.env.RELEASE_SIGNING_PRIVATE_KEY));
  } else if (command === 'verify') {
    verifyArchive(readFileSync(file), readFileSync(`${file}.sig`), process.env.RELEASE_SIGNING_PUBLIC_KEY);
  } else if (command === 'run') {
    validateRun(json(file), process.env.GITHUB_REPOSITORY);
  } else if (command === 'metadata') {
    validateMetadata(json(file), json(runFile), process.env.GITHUB_REPOSITORY);
  } else {
    throw new Error('Expected sign, verify, run, or metadata');
  }
}
