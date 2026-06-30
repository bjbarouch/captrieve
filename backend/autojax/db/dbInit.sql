-- autojax database initialization
-- ─────────────────────────────────────────────────────────────────────────────
-- WARNING: This script drops and recreates autoJaxUser and autoJaxThrottle, erasing all existing data in those tables.
-- This is intended for first-time setup, or repeated use in dev and test environments.
-- Do NOT run this against a production database with existing user data.
--
-- ─────────────────────────────────────────────────────────────────────────────
--
-- New project or wiping clean in dev & test:
--     Run this script to initialize the tables needed by autojax.
--     If your app needs per-user data beyond what autoJaxUser provides, create a separate app-specific user table, align with
--     `autoJaxUser.autoJaxUserId` INT NOT NULL as a foreign key, and JOIN if needed within your own services.
--     Do not add app-specific columns to autoJaxUser, and do not merge bjAuthUser columns into your user table, as this will
--     break on reinstalls and upgrades.
--
-- ─────────────────────────────────────────────────────────────────────────────

-- ─────────────────────────────────────────────────────────────────────────────
-- autoJaxUser: user accounts and auth state
-- Do NOT rename or modify.
-- ─────────────────────────────────────────────────────────────────────────────

DROP TABLE IF EXISTS `autoJaxUser`;
CREATE TABLE `autoJaxUser` (
    `autoJaxUserId`            INT            NOT NULL,
    `displayName_enc`          VARBINARY(282) NOT NULL COMMENT 'AES-256-GCM encrypted; max 254 chars + 28 bytes overhead',
    `displayName_hash`         CHAR(64)       COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SHA-256 hex, always 64 chars',
    `email_enc`                VARBINARY(282) NOT NULL COMMENT 'AES-256-GCM encrypted; max 254 chars + 28 bytes overhead',
    `email_hash`               CHAR(64)       COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SHA-256 hex, always 64 chars',
    `password_hash`            CHAR(60)       COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'bcrypt always produces 60 chars',
    `lastRevocation`           TIMESTAMP      NULL DEFAULT NULL COMMENT 'reject session JWTs issued at or before this (set on logout, password change, admin suspend/terminate)',
    `passwordResetTokenHash`   CHAR(64)       COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'SHA-256 hex of the latest issued password-reset token, NULL once consumed or never issued',
    `passwordResetRequestedAt` TIMESTAMP      NULL DEFAULT NULL COMMENT 'when the last reset link was issued, drives the per-user request cooldown',
    `crossPageTokenHash`       CHAR(64)       COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'SHA-256 hex of the current cross-page (multi-page session) token, NULL once revoked or never issued. rotated on every exchange',
    `crossPageTokenHashPrev`   CHAR(64)       COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'SHA-256 hex of the cross-page token the current one just replaced, accepted within a short grace window so rotation does not break across tabs',
    `crossPageRotatedAt`       TIMESTAMP      NULL DEFAULT NULL COMMENT 'when crossPageTokenHash was last set (when prev became prev), bounds the overlap grace for the previous token',
    `currentSessionHash`       CHAR(64)       COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'single-session mode only (concurrentSessions = 0): SHA-256 hex of the active session id. set at login, NULL once logged out or never set. a token whose sessionId hash does not match this belongs to a session a newer login displaced (last-wins). always NULL in the default unlimited-sessions mode',
    `currentSessionExpiration` TIMESTAMP      NULL DEFAULT NULL COMMENT 'single-session mode only: when the active session reaches its absolute-maximum lifetime. a slot past this no longer matches, so it self-frees lazily without a cron',
    `accountStatus`            ENUM('pending', 'active', 'suspended', 'terminated') NOT NULL DEFAULT 'active' COMMENT 'pending = self-registered awaiting email confirmation; default active for admin/seeded inserts',
    `statusChangedAt`          TIMESTAMP      NULL DEFAULT NULL COMMENT 'set when status changes; for pending rows it marks creation time and drives expiry cleanup',
    `statusChangedBy`          VARCHAR(64)    COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'audit label, e.g. "admin", "self-registration", "email confirmation"',
    `isAdmin`                  TINYINT(1)     NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `autoJaxUser`
    ADD PRIMARY KEY (`autoJaxUserId`),
    ADD UNIQUE KEY `emailHash` (`email_hash`),
    ADD UNIQUE KEY `displayNameHash` (`displayName_hash`);

ALTER TABLE `autoJaxUser`
    MODIFY `autoJaxUserId` INT NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- ─────────────────────────────────────────────────────────────────────────────
-- autoJaxThrottle: throttle counters keyed by (ip, throttleType)
--
-- Do NOT rename or modify.
-- throttleType discriminates the buckets: login, public, client_log, and the two password_reset_* buckets
-- are keyed by client IP; the login_name bucket is keyed by the 64-char login-name hash (so a counter
-- exists for any attempted login name, real or not, which is why the ip column holds 64 chars). the
-- composite primary key ensures one row per key per bucket.
-- ─────────────────────────────────────────────────────────────────────────────

DROP TABLE IF EXISTS `autoJaxThrottle`;
CREATE TABLE `autoJaxThrottle` (
    `ip`           VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'throttle key: an IP (IPv4/IPv6) for ip buckets, or a 64-char login-name hash for the login_name bucket',
    `throttleType` VARCHAR(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'login' COMMENT 'login | login_name | public | client_log | password_reset_request | password_reset_attempt',
    `failCount`    INT         NOT NULL DEFAULT 0,
    `windowStart`  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `autoJaxThrottle`
    ADD PRIMARY KEY (`ip`, `throttleType`);
