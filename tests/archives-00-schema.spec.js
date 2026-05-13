// @ts-check
/**
 * Archives E2E schema preflight.
 *
 * Several archives specs exercise routes/tables directly. This guard makes the
 * plugin activation/schema contract explicit before the alphabetically later
 * archives suites run in the full E2E pass.
 */

const { test, expect } = require('@playwright/test');
const { execFileSync } = require('child_process');

const DB_HOST = process.env.E2E_DB_HOST || '';
const DB_PORT = process.env.E2E_DB_PORT || '';
const DB_USER = process.env.E2E_DB_USER || '';
const DB_PASS = process.env.E2E_DB_PASS ?? '';
const DB_NAME = process.env.E2E_DB_NAME || '';
const DB_SOCKET = process.env.E2E_DB_SOCKET || '';

test.skip(
  !DB_USER || !DB_NAME || (!DB_HOST && !DB_SOCKET),
  'Missing E2E_DB_* configuration (use /tmp/run-e2e.sh)',
);

function runPhp(code) {
  return execFileSync('php', ['-r', code], {
    cwd: process.cwd(),
    encoding: 'utf-8',
    timeout: 30000,
    env: {
      ...process.env,
      MYSQL_PWD: DB_PASS,
    },
  }).trim();
}

test.describe('Archives schema preflight', () => {
  test('archives plugin is active and schema tables exist before archives E2E specs', () => {
    const result = runPhp(`
      require __DIR__ . '/vendor/autoload.php';
      require_once __DIR__ . '/storage/plugins/archives/ArchivesPlugin.php';

      $host = getenv('E2E_DB_HOST') ?: 'localhost';
      $port = getenv('E2E_DB_PORT') ? (int) getenv('E2E_DB_PORT') : 0;
      $socket = getenv('E2E_DB_SOCKET') ?: null;
      $db = new mysqli($host, getenv('E2E_DB_USER'), getenv('E2E_DB_PASS') ?: '', getenv('E2E_DB_NAME'), $port, $socket);
      if ($db->connect_errno) {
          throw new RuntimeException('DB connect failed: ' . $db->connect_error);
      }
      $db->set_charset('utf8mb4');

      $meta = json_decode(file_get_contents(__DIR__ . '/storage/plugins/archives/plugin.json'), true, 512, JSON_THROW_ON_ERROR);
      $metadata = json_encode($meta['metadata'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

      $stmt = $db->prepare(
          'INSERT INTO plugins (
              name, display_name, description, version, author, author_url, plugin_url,
              is_active, path, main_file, requires_php, requires_app, metadata, installed_at, activated_at
           ) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, NOW(), NOW())
           ON DUPLICATE KEY UPDATE
              display_name = VALUES(display_name),
              description = VALUES(description),
              version = VALUES(version),
              author = VALUES(author),
              author_url = VALUES(author_url),
              plugin_url = VALUES(plugin_url),
              is_active = 1,
              path = VALUES(path),
              main_file = VALUES(main_file),
              requires_php = VALUES(requires_php),
              requires_app = VALUES(requires_app),
              metadata = VALUES(metadata),
              activated_at = NOW()'
      );
      if ($stmt === false) {
          throw new RuntimeException('Plugin upsert prepare failed: ' . $db->error);
      }

      $name = $meta['name'];
      $displayName = $meta['display_name'] ?? $name;
      $description = $meta['description'] ?? '';
      $version = $meta['version'] ?? '1.0.0';
      $author = $meta['author'] ?? '';
      $authorUrl = $meta['author_url'] ?? '';
      $pluginUrl = $meta['plugin_url'] ?? '';
      $path = $meta['path'] ?? $name;
      $mainFile = $meta['main_file'] ?? 'wrapper.php';
      $requiresPhp = $meta['requires_php'] ?? '';
      $requiresApp = $meta['requires_app'] ?? '';

      $stmt->bind_param(
          'ssssssssssss',
          $name,
          $displayName,
          $description,
          $version,
          $author,
          $authorUrl,
          $pluginUrl,
          $path,
          $mainFile,
          $requiresPhp,
          $requiresApp,
          $metadata
      );
      if (!$stmt->execute()) {
          throw new RuntimeException('Plugin upsert failed: ' . $stmt->error);
      }
      $stmt->close();

      $idResult = $db->query("SELECT id FROM plugins WHERE name = 'archives' LIMIT 1");
      $pluginId = (int) ($idResult?->fetch_assoc()['id'] ?? 0);
      if ($pluginId <= 0) {
          throw new RuntimeException('Archives plugin row missing after upsert');
      }

      $plugin = new App\\Plugins\\Archives\\ArchivesPlugin($db, new App\\Support\\HookManager($db));
      $plugin->setPluginId($pluginId);
      $plugin->onActivate();

      $required = ['archival_units', 'authority_records', 'archival_unit_authority', 'archival_unit_files'];
      $missing = [];
      foreach ($required as $table) {
          $tableEsc = $db->real_escape_string($table);
          $res = $db->query("SHOW TABLES LIKE '{$tableEsc}'");
          if (!$res || $res->num_rows === 0) {
              $missing[] = $table;
          }
      }
      echo json_encode(['ok' => $missing === [], 'missing' => $missing], JSON_THROW_ON_ERROR);
    `);

    const parsed = JSON.parse(result);
    expect(parsed, result).toEqual({ ok: true, missing: [] });
  });
});
