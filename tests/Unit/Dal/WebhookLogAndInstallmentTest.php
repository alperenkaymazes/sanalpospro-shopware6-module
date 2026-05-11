<?php declare(strict_types=1);

namespace SanalposproPayment\Tests\Unit\Dal;

use PHPUnit\Framework\TestCase;

use SanalposproPayment\Core\Content\WebhookLog\WebhookLogCollection;
use SanalposproPayment\Core\Content\WebhookLog\WebhookLogDefinition;
use SanalposproPayment\Core\Content\WebhookLog\WebhookLogEntity;

use SanalposproPayment\Core\Content\Installment\InstallmentCollection;
use SanalposproPayment\Core\Content\Installment\InstallmentDefinition;
use SanalposproPayment\Core\Content\Installment\InstallmentEntity;

use SanalposproPayment\Migration\Migration1710000000InstallmentAndLog;

/**
 * Unit testleri — Task 1.1: Database Layer (DAL)
 *
 * Bu testler gerçek bir veritabanı GEREKTIRMEZ.
 * PHP sınıflarının yapısını, sabit değerleri ve entity getter/setter'larını kontrol eder.
 *
 * Çalıştırmak için:
 *   vendor/bin/phpunit tests/Unit/Dal/WebhookLogAndInstallmentTest.php
 */
class WebhookLogAndInstallmentTest extends TestCase
{
    // =========================================================================
    //  WebhookLogDefinition
    // =========================================================================

    /** Entity adı Contract C'de tanımlanan tablo adıyla eşleşmeli */
    public function testWebhookLogEntityNameMatchesContractC(): void
    {
        $this->assertSame(
            'sanalpospro_webhook_log',
            WebhookLogDefinition::ENTITY_NAME,
            'WebhookLogDefinition::ENTITY_NAME Contract C\'deki tablo adıyla eşleşmiyor.'
        );
    }

    /** Definition, doğru Entity sınıfını döndürmeli */
    public function testWebhookLogDefinitionReturnsCorrectEntityClass(): void
    {
        $definition = new WebhookLogDefinition();

        $this->assertSame(
            WebhookLogEntity::class,
            $definition->getEntityClass()
        );
    }

    /** Definition, doğru Collection sınıfını döndürmeli */
    public function testWebhookLogDefinitionReturnsCorrectCollectionClass(): void
    {
        $definition = new WebhookLogDefinition();

        $this->assertSame(
            WebhookLogCollection::class,
            $definition->getCollectionClass()
        );
    }

    // =========================================================================
    //  WebhookLogEntity — getter / setter
    // =========================================================================

    /** orderTxId getter/setter çalışmalı */
    public function testWebhookLogEntityOrderTxId(): void
    {
        $entity = new WebhookLogEntity();
        $entity->setOrderTxId('abc-123');

        $this->assertSame('abc-123', $entity->getOrderTxId());
    }

    /** paythorTxId getter/setter çalışmalı */
    public function testWebhookLogEntityPaythorTxId(): void
    {
        $entity = new WebhookLogEntity();
        $entity->setPaythorTxId('ptx-456');

        $this->assertSame('ptx-456', $entity->getPaythorTxId());
    }

    /** action alanı 'webhook' veya 'callback' olabilmeli */
    public function testWebhookLogEntityAction(): void
    {
        $entity = new WebhookLogEntity();

        $entity->setAction('webhook');
        $this->assertSame('webhook', $entity->getAction());

        $entity->setAction('callback');
        $this->assertSame('callback', $entity->getAction());
    }

    /** status alanı Contract C değerlerini kabul etmeli */
    public function testWebhookLogEntityStatus(): void
    {
        $entity = new WebhookLogEntity();
        $allowedStatuses = ['success', 'failed', 'pending', 'refunded'];

        foreach ($allowedStatuses as $status) {
            $entity->setStatus($status);
            $this->assertSame($status, $entity->getStatus(), "Status '$status' beklendiği gibi ayarlanamadı.");
        }
    }

    /** amount alanı float değeri tutmalı */
    public function testWebhookLogEntityAmount(): void
    {
        $entity = new WebhookLogEntity();
        $entity->setAmount(149.99);

        $this->assertSame(149.99, $entity->getAmount());
    }

    /** currency alanı 3 karakterlik ISO kodu tutmalı */
    public function testWebhookLogEntityCurrency(): void
    {
        $entity = new WebhookLogEntity();
        $entity->setCurrency('TRY');

        $this->assertSame('TRY', $entity->getCurrency());
    }

    /** rawPayload alanı JSON string tutmalı */
    public function testWebhookLogEntityRawPayload(): void
    {
        $entity  = new WebhookLogEntity();
        $payload = json_encode(['status' => 'success', 'amount' => 100]);
        $entity->setRawPayload($payload);

        $this->assertSame($payload, $entity->getRawPayload());
        $this->assertJson($entity->getRawPayload(), 'rawPayload geçerli bir JSON içermiyor.');
    }

    /** Tüm nullable alanlar başlangıçta null olmalı */
    public function testWebhookLogEntityDefaultsAreNull(): void
    {
        $entity = new WebhookLogEntity();

        $this->assertNull($entity->getOrderTxId());
        $this->assertNull($entity->getPaythorTxId());
        $this->assertNull($entity->getAction());
        $this->assertNull($entity->getStatus());
        $this->assertNull($entity->getAmount());
        $this->assertNull($entity->getCurrency());
        $this->assertNull($entity->getRawPayload());
    }

    // =========================================================================
    //  WebhookLogCollection
    // =========================================================================

    /** Boş koleksiyon oluşturulabilmeli */
    public function testWebhookLogCollectionIsEmpty(): void
    {
        $collection = new WebhookLogCollection();

        $this->assertCount(0, $collection);
    }

    /** Koleksiyona entity eklenebilmeli ve geri alınabilmeli */
    public function testWebhookLogCollectionCanAddEntity(): void
    {
        $entity = new WebhookLogEntity();
        $entity->setId('11111111-1111-1111-1111-111111111111');
        $entity->setStatus('success');

        $collection = new WebhookLogCollection([$entity]);

        $this->assertCount(1, $collection);
        $this->assertSame('success', $collection->first()->getStatus());
    }

    // =========================================================================
    //  InstallmentDefinition
    // =========================================================================

    /** Entity adı Contract C'de tanımlanan tablo adıyla eşleşmeli */
    public function testInstallmentEntityNameMatchesContractC(): void
    {
        $this->assertSame(
            'sanalpospro_installment',
            InstallmentDefinition::ENTITY_NAME,
            'InstallmentDefinition::ENTITY_NAME Contract C\'deki tablo adıyla eşleşmiyor.'
        );
    }

    /** Definition, doğru Entity sınıfını döndürmeli */
    public function testInstallmentDefinitionReturnsCorrectEntityClass(): void
    {
        $definition = new InstallmentDefinition();

        $this->assertSame(
            InstallmentEntity::class,
            $definition->getEntityClass()
        );
    }

    /** Definition, doğru Collection sınıfını döndürmeli */
    public function testInstallmentDefinitionReturnsCorrectCollectionClass(): void
    {
        $definition = new InstallmentDefinition();

        $this->assertSame(
            InstallmentCollection::class,
            $definition->getCollectionClass()
        );
    }

    // =========================================================================
    //  InstallmentEntity — getter / setter
    // =========================================================================

    /** bankName getter/setter çalışmalı */
    public function testInstallmentEntityBankName(): void
    {
        $entity = new InstallmentEntity();
        $entity->setBankName('Garanti BBVA');

        $this->assertSame('Garanti BBVA', $entity->getBankName());
    }

    /** cardType getter/setter çalışmalı */
    public function testInstallmentEntityCardType(): void
    {
        $entity = new InstallmentEntity();
        $entity->setCardType('mastercard');

        $this->assertSame('mastercard', $entity->getCardType());
    }

    /** installmentCount pozitif tamsayı tutmalı */
    public function testInstallmentEntityInstallmentCount(): void
    {
        $entity = new InstallmentEntity();

        foreach ([3, 6, 9, 12] as $count) {
            $entity->setInstallmentCount($count);
            $this->assertSame($count, $entity->getInstallmentCount());
        }
    }

    /** interestRate ondalıklı oran tutmalı */
    public function testInstallmentEntityInterestRate(): void
    {
        $entity = new InstallmentEntity();
        $entity->setInterestRate(1.95);

        $this->assertSame(1.95, $entity->getInterestRate());
    }

    /** interestRate sıfır olabilmeli (faizsiz taksit) */
    public function testInstallmentEntityInterestRateCanBeZero(): void
    {
        $entity = new InstallmentEntity();
        $entity->setInterestRate(0.0);

        $this->assertSame(0.0, $entity->getInterestRate());
    }

    /** isActive true/false tutmalı */
    public function testInstallmentEntityIsActive(): void
    {
        $entity = new InstallmentEntity();

        $entity->setIsActive(true);
        $this->assertTrue($entity->getIsActive());

        $entity->setIsActive(false);
        $this->assertFalse($entity->getIsActive());
    }

    /** Tüm nullable alanlar başlangıçta null olmalı */
    public function testInstallmentEntityDefaultsAreNull(): void
    {
        $entity = new InstallmentEntity();

        $this->assertNull($entity->getBankName());
        $this->assertNull($entity->getCardType());
        $this->assertNull($entity->getInstallmentCount());
        $this->assertNull($entity->getInterestRate());
        $this->assertNull($entity->getIsActive());
    }

    // =========================================================================
    //  InstallmentCollection
    // =========================================================================

    /** Boş koleksiyon oluşturulabilmeli */
    public function testInstallmentCollectionIsEmpty(): void
    {
        $collection = new InstallmentCollection();

        $this->assertCount(0, $collection);
    }

    /** Koleksiyona birden fazla entity eklenebilmeli */
    public function testInstallmentCollectionCanHoldMultipleEntities(): void
    {
        $e1 = new InstallmentEntity();
        $e1->setId('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa');
        $e1->setBankName('Garanti BBVA');
        $e1->setInstallmentCount(3);

        $e2 = new InstallmentEntity();
        $e2->setId('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb');
        $e2->setBankName('İş Bankası');
        $e2->setInstallmentCount(6);

        $collection = new InstallmentCollection([$e1, $e2]);

        $this->assertCount(2, $collection);
        $this->assertSame('Garanti BBVA', $collection->first()->getBankName());
        $this->assertSame('İş Bankası', $collection->last()->getBankName());
    }

    // =========================================================================
    //  Migration
    // =========================================================================

    /** Migration timestamp doğru olmalı */
    public function testMigrationTimestamp(): void
    {
        $migration = new Migration1710000000InstallmentAndLog();

        $this->assertSame(1710000000, $migration->getCreationTimestamp());
    }

    /** Migration, MigrationStep'i extend etmeli */
    public function testMigrationExtendsMigrationStep(): void
    {
        $migration = new Migration1710000000InstallmentAndLog();

        $this->assertInstanceOf(
            \Shopware\Core\Framework\Migration\MigrationStep::class,
            $migration
        );
    }

    /** Migration sınıfı update() metoduna sahip olmalı */
    public function testMigrationHasUpdateMethod(): void
    {
        $this->assertTrue(
            method_exists(Migration1710000000InstallmentAndLog::class, 'update'),
            'Migration sınıfında update() metodu bulunamadı.'
        );
    }

    /** Migration sınıfı updateDestructive() metoduna sahip olmalı (rollback için) */
    public function testMigrationHasUpdateDestructiveMethod(): void
    {
        $this->assertTrue(
            method_exists(Migration1710000000InstallmentAndLog::class, 'updateDestructive'),
            'Migration sınıfında updateDestructive() metodu bulunamadı.'
        );
    }
}
