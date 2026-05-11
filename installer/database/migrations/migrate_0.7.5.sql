-- Pinakes v0.7.5 — French locale (fr_FR) + i18n LibraryThing fixes
-- Ensures fr_FR language row exists for installs upgrading from 0.7.4.
-- New installs get fr_FR via data_XX.sql seeder.
-- INSERT IGNORE is idempotent: safe to run even if the row already exists.

INSERT IGNORE INTO `languages`
    (`code`, `name`, `native_name`, `flag_emoji`, `is_default`, `is_active`, `translation_file`, `total_keys`, `translated_keys`, `completion_percentage`)
VALUES
    ('fr_FR', 'French', 'Français', '🇫🇷', 0, 1, 'locale/fr_FR.json', 4145, 4145, 100.00);
