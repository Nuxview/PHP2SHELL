# PHP memory lab

A Laravel 12 app with an interactive, conceptual walkthrough of PHP2Shell. Runs on patched PHP 8.5.12, pinned by container digest. This is a local learning app, not a vulnerable target or an exploit reproduction. It uses Laravel's development server, not mod_php or FPM.

## Run with Docker

```sh
cp .env.example .env
docker compose run --rm app composer install --no-interaction
docker compose run --rm app php artisan key:generate
docker compose up
```

Open http://127.0.0.1:8000. No database, Node build, or external account is required. Stop with Ctrl+C and `docker compose down`.

The image includes Composer and PHP for a convenient local setup; it is not a production deployment image. Dependencies are pinned in composer.lock. Keep PHP and dependencies updated after this snapshot.

## Walkthrough

1. Open the app and choose out-of-memory corruption (CVE-2026-109618). Follow initialization, interruption, and cleanup. The old PHP engine could leave incomplete state that cleanup treated as valid.
2. Choose stale global state (CVE-2026-109619). Follow request A freeing its data and request B using a reference that survived. Both requests must reach the same persistent worker in the demonstrated paths.
3. Compare the consequences: crashes, sensitive memory disclosure, or code running with the PHP worker's privileges. Access to database credentials and writable site files can extend the damage. Operating-system root access is not automatic.
4. Connect this to WordPress or Laravel: they run on the PHP interpreter. A flaw reached before their code starts cannot be fixed by application authentication or a framework update.

The diagram only advances through text states in your browser. It does not allocate excessive memory, corrupt native memory, or issue attack requests. The patched app cannot establish whether another server is vulnerable. Different server interfaces and configurations have different reachable paths; a demonstrated mod_php exploit does not establish an identical FPM exploit.

## What to do

Site operators should update the PHP engine and restart all web-serving workers. The October 21 fixes are PHP 8.2.35, 8.3.36, 8.4.27, and 8.5.12 within their respective branches (or subsequent patched releases). Vendor backports may keep older-looking package versions: verify the vendor advisory. Check the actual web runtime, not only `php -v` in a terminal. WordPress, plugin, Laravel, or Composer updates alone do not patch the interpreter. Temporary settings and WAFs are not complete substitutes for the PHP update.

Visitors do not execute the server's PHP interpreter on their own devices. Their risk is indirect: a compromised site can capture submitted data, misuse sessions, or distribute malicious content. For sites you manage, request confirmation from the host that both CVEs are patched and workers restarted. For accounts on affected services, follow the service's incident guidance; these flaws alone do not prove your account was compromised.

## Sources

- [PHP security announcement, October 21, 2026](https://www.php.net/archive/2026.php#2026-10-21-5)
- [CVE-2026-109618 / OOM corruption](https://github.com/php/php-src/security/advisories/GHSA-w8hw-w9fx-j79w)
- [CVE-2026-109619 / stale global state](https://github.com/php/php-src/security/advisories/GHSA-6wmf-4rv5-p3rp)

## Verification

```sh
docker compose run --rm app composer validate --strict
docker compose run --rm app php artisan test
docker compose run --rm app php -r 'echo PHP_VERSION, PHP_EOL;'
```

## Repository

Source: https://github.com/Nuxview/PHP2SHELL

```sh
git clone https://github.com/Nuxview/PHP2SHELL.git
cd PHP2SHELL
```

Then follow the Docker instructions above. The repository is public. Local `.env` files, generated secrets, and installed dependencies are excluded from Git.
