<?php declare(strict_types=1);

namespace SanalposproPayment\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * Creates `sanalpospro_webhook_log` and `sanalpospro_installment` tables.
 *
 * Column names match Contract C exactly (README §Contract C).
 *
 * Rollback via updateDestructive() drops both tables cleanly.
 *
 * Equivalent to db_schema.xml (paythor_transaction_log) from Magento — now
 * written as raw DBAL SQL following Shopware's migration pattern.
 */
class Migration1710000000InstallmentAndLog extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1710000000;
    }

    public function update(Connection $connection): void
    {
        // ------------------------------------------------------------------
        // Table: sanalpospro_webhook_log
        // Maps to: paythor_transaction_log in Magento (Contract C)
        // ------------------------------------------------------------------
        $connection->executeStatement(<<<SQL
            CREATE TABLE IF NOT EXISTS `sanalpospro_webhook_log` (
                `id`            BINARY(16)       NOT NULL,
                `order_tx_id`   VARCHAR(128)     NOT NULL                COMMENT 'Shopware order_transaction.id',
                `paythor_tx_id` VARCHAR(128)     NULL                    COMMENT 'PayThor gateway transaction_id',
                `action`        VARCHAR(32)      NOT NULL                COMMENT '"webhook" | "callback"',
                `status`        VARCHAR(32)      NOT NULL                COMMENT '"success" | "failed" | "pending" | "refunded"',
                `amount`        DECIMAL(20, 4)   NULL,
                `currency`      CHAR(3)          NULL,
                `raw_payload`   LONGTEXT         NULL                    COMMENT 'Full incoming JSON',
                `created_at`    DATETIME(3)      NOT NULL,
                `updated_at`    DATETIME(3)      NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_webhook_log_order_tx_id` (`order_tx_id`),
                INDEX `idx_webhook_log_status`      (`status`),
                INDEX `idx_webhook_log_created_at`  (`created_at`)
            )
            ENGINE = InnoDB
            DEFAULT CHARSET = utf8mb4
            COLLATE = utf8mb4_unicode_ci;
        SQL);

        // ------------------------------------------------------------------
        // Table: sanalpospro_installment
        // New entity (was Block/Product/Installments.php + .phtml in Magento)
        // Now a proper Shopware DAL entity for Vue.js CRUD Admin module.
        // ------------------------------------------------------------------
        $connection->executeStatement(<<<SQL
            CREATE TABLE IF NOT EXISTS `sanalpospro_installment` (
                `id`                BINARY(16)      NOT NULL,
                `bank_name`         VARCHAR(128)    NOT NULL,
                `card_type`         VARCHAR(64)     NULL,
                `installment_count` INT             NOT NULL,
                `interest_rate`     DECIMAL(5, 2)   NOT NULL DEFAULT 0.00,
                `is_active`         TINYINT(1)      NOT NULL DEFAULT 1,
                `created_at`        DATETIME(3)     NOT NULL,
                `updated_at`        DATETIME(3)     NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_installment_bank_name`  (`bank_name`),
                INDEX `idx_installment_is_active`  (`is_active`)
            )
            ENGINE = InnoDB
            DEFAULT CHARSET = utf8mb4
            COLLATE = utf8mb4_unicode_ci;
        SQL);
    }

    /**
     * Drops both tables cleanly (used by bin/console database:migrate:rollback).
     */
    public function updateDestructive(Connection $connection): void
    {
        $connection->executeStatement('DROP TABLE IF EXISTS `sanalpospro_webhook_log`');
        $connection->executeStatement('DROP TABLE IF EXISTS `sanalpospro_installment`');
    }
}
