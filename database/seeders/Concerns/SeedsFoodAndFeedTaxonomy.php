<?php

namespace Database\Seeders\Concerns;

use App\Company;

trait SeedsFoodAndFeedTaxonomy
{
    use ClearsFoodRelatedTaxonomyData;
    use ReadsAmSpecParametersSpreadsheet;
    use SeedsAmSpecParameterMatrix;

    public const FOOD_TAXONOMY_PATH = 'database/seeders/data/food_and_feed_taxonomy_rows.php';

    /**
     * @return array{rows: int, sample_types: int, analysis_types: int, analytes: int, elements: int, methods: int}
     */
    protected function seedFoodAndFeedTaxonomy(Company $company): array
    {
        $this->clearFoodRelatedTaxonomyData($company);

        return $this->importAmSpecParameterRows(
            $company,
            $this->readAmSpecParameterRows(self::FOOD_TAXONOMY_PATH)
        );
    }
}
