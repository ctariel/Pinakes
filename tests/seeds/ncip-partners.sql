-- NCIP partner seed: 3 mock libraries for E2E tests
-- Run: mysql -u USER DB < tests/seeds/ncip-partners.sql

SET NAMES utf8mb4;

INSERT INTO ncip_partners (code, name, endpoint_url, isil, notes, created_at, updated_at) VALUES
    ('E2E_BNCR', 'Biblioteca Nazionale Centrale di Roma',    'https://bncr.example.org/ncip',  'IT-RM001', 'Partner di test #1', NOW(), NOW()),
    ('E2E_BNCF', 'Biblioteca Nazionale Centrale di Firenze', 'https://bncf.example.org/ncip',  'IT-FI001', 'Partner di test #2', NOW(), NOW()),
    ('E2E_BAV',  'Biblioteca Apostolica Vaticana',           'https://bav.example.org/ncip',   'IT-VA001', 'Partner di test #3', NOW(), NOW())
ON DUPLICATE KEY UPDATE
    name         = VALUES(name),
    endpoint_url = VALUES(endpoint_url),
    isil         = VALUES(isil),
    notes        = VALUES(notes),
    updated_at   = NOW();
