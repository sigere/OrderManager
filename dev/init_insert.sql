TRUNCATE TABLE `user`;
INSERT INTO `user` VALUES 
(
    1,
    NULL,
    'admin',
    '[\"ROLE_USER\", \"ROLE_ADMIN\"]',
    '$argon2id$v=19$m=16,t=2,p=1$UjBOZUdsZ3E0RFc1U3BqTw$lwY1VMjdwlDL+37w8jjrBA',
    'Admin',
    'Admin',
    '{\"index\":{\"przyjete\":true, \"wykonane\":true, \"wyslane\":true, \"adoption\":false, \"client\":false, \"topic\":true, \"lang\":false, \"deadline\":true, \"staff\":true, \"select-client\":null, \"select-staff\":null, \"date-type\":\"deadline\", \"date-from\":null, \"date-to\":null}, \"archives\":{\"usuniete\":false, \"adoption\":true, \"client\":true, \"topic\":true, \"lang\":true, \"deadline\":true, \"staff\":true, \"select-client\":7, \"select-staff\":null, \"date-type\":\"deadline\", \"date-from\":null, \"date-to\":null}}',
    NOW(),
    NULL
);

TRUNCATE TABLE `company`;

INSERT INTO `company` VALUES
(
    1,
    'Moja firma',
    '123456789',
    'ul. Krakowska 10',
    '00-000',
    'Warszawa',
    '14235698765432146490000001',
    NOW(),
    NOW(),
    'notatka - kliknij mnie',
    NOW()
); 