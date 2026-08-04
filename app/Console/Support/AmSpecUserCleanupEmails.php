<?php

namespace App\Console\Support;

use Database\Seeders\Concerns\AmSpecSeedData;

class AmSpecUserCleanupEmails
{
    /**
     * LIMS sheet users (Middle East Agri & Food roster).
     *
     * @return list<string>
     */
    public static function limsSheetEmails(): array
    {
        return [
            'imran.khan@amspecgroup.com',
            'hassane.sidaoui@amspecgroup.com',
            'sujuta.gurung@amspecgroup.com',
            'subin.paul@amspecgroup.com',
            'iqrar.ali@amspecgroup.com',
            'jishma.panichikkal@amspecgroup.com',
            'mohammadziya.zaidi@amspecgroup.com',
            'dipendra.pradhan@amspecgroup.com',
            'mohamed.mustkeem@amspecgroup.com',
            'abbas.soortee@amspecgroup.com',
        ];
    }

    /**
     * Always-retained operators (in addition to the LIMS sheet roster).
     *
     * @return list<string>
     */
    public static function retainedOperatorEmails(): array
    {
        return [
            AmSpecSeedData::SEED_USER_EMAIL, // Ernest Kamau
            AmSpecSeedData::COLEMAN_SEED_EMAIL, // Coleman / Colman (seed email; may not exist yet)
            'kamauernest06+staff7@gmail.com', // Coleman (current amspec_brazil4 row)
            'nancykaroki49@gmail.com', // Nancy Karoki
            'karokin35@gmail.com', // Karokin Portal
            'fridahk25@gmail.com', // Fridah Nyaga
            'fridahk25+test1@gmail.com', // Fridah fridah
        ];
    }

    /**
     * Display names that must also be retained (decrypted `users.name`).
     *
     * @return list<string>
     */
    public static function retainedOperatorNames(): array
    {
        return [
            'Nancy Wanjiku Karoki',
            'Nancy Karoki',
            'Karokin Portal',
            'Coleman',
            'Ernest Kamau',
            'Fridah fridah',
            'Fridah Nyaga',
        ];
    }

    /**
     * Users kept by users:delete-except-retained.
     *
     * @return list<string>
     */
    public static function retainedEmails(): array
    {
        return array_values(array_unique(array_merge(
            self::limsSheetEmails(),
            self::retainedOperatorEmails(),
        )));
    }

    /**
     * @return list<string>
     */
    public static function normalized(array $emails): array
    {
        return array_values(array_unique(array_map(
            static fn (string $email): string => strtolower(trim($email)),
            $emails,
        )));
    }
}
