<?php

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Tests\Functional\Repository;

use JWeiland\Glossary2\Domain\Model\Glossary;
use JWeiland\Glossary2\Domain\Repository\GlossaryRepository;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;

/**
 * Test case.
 */
class GlossaryRepositoryTest extends FunctionalTestCase
{
    /**
     * @var GlossaryRepository
     */
    protected $subject;

    /**
     * @var QuerySettingsInterface
     */
    protected $querySettings;

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tx_glossary2_domain_model_glossary.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/sys_category.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/sys_category_record_mm.csv');

        $this->querySettings = GeneralUtility::makeInstance(QuerySettingsInterface::class);
        $this->querySettings->setStoragePageIds([12]);
        $this->subject = GeneralUtility::makeInstance(GlossaryRepository::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->objectManager
        );
        parent::tearDown();
    }

    /**
     * @test
     */
    public function findAllWillFindGlossariesSorted(): void
    {
        $glossaries = [];
        /** @var Glossary $glossary */
        foreach ($this->subject->findAll() as $glossary) {
            $glossaries[] = $glossary->getTitle();
        }
        $sortedGlossaries = $glossaries;
        sort($sortedGlossaries);

        self::assertSame(
            $sortedGlossaries,
            $glossaries
        );
    }

    /**
     * @test
     */
    public function searchGlossariesWithInvalidCategoriesWillFindAllEntries(): void
    {
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertCount(
            7,
            $this->subject->searchGlossaries(['0', 'a'])->toArray()
        );
    }

    /**
     * @test
     */
    public function searchGlossariesWithGivenCategoryWillFindTwoEntries(): void
    {
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertCount(
            2,
            $this->subject->searchGlossaries([1])->toArray()
        );
    }

    /**
     * @test
     */
    public function searchGlossariesWithSomeInvalidCategoriesWillFindAllEntries(): void
    {
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertCount(
            7,
            $this->subject->searchGlossaries(['0', 'a', 1])->toArray()
        );
    }

    /**
     * @test
     */
    public function searchGlossariesWithLetterWillFindTwoEntries(): void
    {
        // "u" will find records with "u" and "ü"
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertCount(
            2,
            $this->subject->searchGlossaries([], 'u')->toArray()
        );
    }

    /**
     * @test
     */
    public function searchGlossariesWithInvalidLetterWillFindAllEntries(): void
    {
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertCount(
            7,
            $this->subject->searchGlossaries([], '/')->toArray()
        );
    }

    /**
     * @test
     */
    public function searchGlossariesWithCategoryAndLetterWillFindOneEntry(): void
    {
        // "u" will find records with "u" and "ü"
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertCount(
            1,
            $this->subject->searchGlossaries([2], 'u')->toArray()
        );
    }
}
