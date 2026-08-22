<?php

namespace App\Support;

class UserManualRegistry
{
    /**
     * @return array<string, array{title: string, subtitle: string, icon: string, lottie: string, chapters: list<array{slug: string, title: string, summary: string}>}>
     */
    public static function manuals(): array
    {
        return [
            'quotations' => [
                'title' => 'Quotations & Pricelist',
                'subtitle' => 'How quotes, prices, approvals, and PDFs fit together',
                'icon' => 'mdi-file-document-outline',
                'lottie' => 'https://assets10.lottiefiles.com/packages/lf20_jcikwtux.json',
                'chapters' => [
                    ['slug' => 'overview', 'title' => 'Overview', 'summary' => 'What a quotation and a pricelist are, and how they connect.'],
                    ['slug' => 'tabs', 'title' => 'Quotation tabs', 'summary' => 'In Preparation, In Approval, Complete, and Approval settings.'],
                    ['slug' => 'process-enquiry', 'title' => 'Process request (pricing)', 'summary' => 'After receiving: set prices, build new or use existing, send for approval or to customer.'],
                    ['slug' => 'approval', 'title' => 'Approval & notifications', 'summary' => 'Configure approvers, send for approval, approve and send or reject.'],
                    ['slug' => 'preparation', 'title' => 'Quote preparation & math', 'summary' => 'Line items, pricelist prices, per test vs package, VAT, PDF columns.'],
                    ['slug' => 'import', 'title' => 'Import AmSpec template', 'summary' => 'Bring an AmSpec quotation template into a preparing quote.'],
                    ['slug' => 'billing-workflow', 'title' => 'Billing quotation workflow', 'summary' => 'From Add Quotation to complete, step by step.'],
                    ['slug' => 'pricelist', 'title' => 'Pricelist in depth', 'summary' => 'Customers, per test / package pricing, apply changes, import.'],
                ],
            ],
            'direct-registration' => [
                'title' => 'Direct Registration',
                'subtitle' => 'Walk-in intake and carousel to receive samples',
                'icon' => 'mdi-account-plus-outline',
                'lottie' => 'https://assets9.lottiefiles.com/packages/lf20_u4yrau.json',
                'chapters' => [
                    ['slug' => 'overview', 'title' => 'What it is', 'summary' => 'Start from Ready for Reception / Actions, then open a TRF.'],
                    ['slug' => 'steps', 'title' => 'Steps & carousel', 'summary' => 'Fill the form, save, and slide to Receive Samples.'],
                    ['slug' => 'after-save', 'title' => 'After you save', 'summary' => 'Integrity check and where the request goes next.'],
                ],
            ],
            'request-view' => [
                'title' => 'Request View Page',
                'subtitle' => 'Read and act on a full testing request',
                'icon' => 'mdi-clipboard-text-outline',
                'lottie' => 'https://assets2.lottiefiles.com/packages/lf20_qp1q7mct.json',
                'chapters' => [
                    ['slug' => 'overview', 'title' => 'Page at a glance', 'summary' => 'Header, client info, and tabs — how to read the page.'],
                    ['slug' => 'header-actions', 'title' => 'Header actions', 'summary' => 'Process enquiry and the Actions menu.'],
                    ['slug' => 'tabs', 'title' => 'Tabs & capabilities', 'summary' => 'Tests, sample collection, notes, and attachments.'],
                    ['slug' => 'editing', 'title' => 'Editing details', 'summary' => 'What you can change and when.'],
                ],
            ],
            'sample-receiving' => [
                'title' => 'Sample Receiving',
                'subtitle' => 'Board statuses, menus, and how requests move',
                'icon' => 'mdi-inbox-arrow-down',
                'lottie' => 'https://assets5.lottiefiles.com/packages/lf20_jbrwoxgx.json',
                'chapters' => [
                    ['slug' => 'overview', 'title' => 'The receiving board', 'summary' => 'Cards, tabs, icons, and origin badges.'],
                    ['slug' => 'statuses', 'title' => 'Statuses & moves', 'summary' => 'What you can do on each tab.'],
                    ['slug' => 'dropdown', 'title' => 'Actions menu', 'summary' => 'Board Actions explained.'],
                    ['slug' => 'flow', 'title' => 'Moving a request forward', 'summary' => 'Receive samples and continue the path.'],
                ],
            ],
        ];
    }

    public static function find(string $slug): ?array
    {
        $manuals = self::manuals();

        return $manuals[$slug] ?? null;
    }

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_keys(self::manuals());
    }
}
