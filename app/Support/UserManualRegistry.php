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
                    ['slug' => 'overview', 'title' => 'Overview', 'summary' => 'Quotations vs pricelists; price with a list or without one.'],
                    ['slug' => 'tabs', 'title' => 'Quotation tabs', 'summary' => 'Overview KPIs; In Preparation, In Approval, Complete, Approval configuration.'],
                    ['slug' => 'process-enquiry', 'title' => 'Process request (pricing)', 'summary' => 'After receiving: set prices, build new or use existing, send for approval or to customer.'],
                    ['slug' => 'approval', 'title' => 'Approval & notifications', 'summary' => 'Move To workflow, In Approval, approve/reject, Complete, and notification channels.'],
                    ['slug' => 'preparation', 'title' => 'Quote preparation & math', 'summary' => 'Without pricelist (per package default, per parameter); append from pricelist; conditions and detach.'],
                    ['slug' => 'import', 'title' => 'Import AmSpec template', 'summary' => 'AmSpec import for quotations and pricelists — template, modes, warnings, Apply Price Changes.'],
                    ['slug' => 'billing-workflow', 'title' => 'Billing quotation workflow', 'summary' => 'Add → prepare → approve → Complete → Create enquiry from quotation wizard.'],
                    ['slug' => 'pricelist', 'title' => 'Pricelist in depth', 'summary' => 'Import, assign customers, add items, cost vs selling price, profit, Internal/External flags.'],
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
            'inventory' => [
                'title' => 'Inventory',
                'subtitle' => 'Set up stock, then buy and receive supplies',
                'icon' => 'mdi-warehouse',
                'lottie' => 'https://assets9.lottiefiles.com/packages/lf20_w51pcehl.json',
                'chapters' => [
                    ['slug' => 'overview', 'title' => 'Welcome', 'summary' => 'What Inventory covers and how this guide is organised.'],
                    ['slug' => 'locations', 'title' => 'Locations', 'summary' => 'Sites where your stock is managed.'],
                    ['slug' => 'organisations', 'title' => 'Organisations (departments)', 'summary' => 'Departments and who belongs where.'],
                    ['slug' => 'stores', 'title' => 'Stores', 'summary' => 'Where items are kept on site.'],
                    ['slug' => 'suppliers-and-categories', 'title' => 'Suppliers & categories', 'summary' => 'Vendors and how items are grouped.'],
                    ['slug' => 'items', 'title' => 'Items', 'summary' => 'The products you request and receive.'],
					['slug' => 'configurations', 'title' => 'Configurations', 'summary' => 'Sidebar settings: material types, currencies, and conversions.'],
                    ['slug' => 'request-to-order', 'title' => 'Request to order', 'summary' => 'Day-to-day buying: from request to goods in store.'],
                    ['slug' => 'purchase-request', 'title' => 'Purchase Request', 'summary' => 'Ask for items and send for approval.'],
                    ['slug' => 'request-for-quotation', 'title' => 'Request for Quotation', 'summary' => 'Send RFQs to suppliers, record quotes, award, approve, and create a PO.'],
                    ['slug' => 'purchase-order', 'title' => 'Purchase Order', 'summary' => 'Approve, send PO to supplier, view PDF, and create full or partial goods receipts.'],
                    ['slug' => 'goods-receipt', 'title' => 'Goods Receipt', 'summary' => 'Verify delivery, accept goods with OTP, rate supplier, and send to finance.'],
                    ['slug' => 'request-to-store', 'title' => 'Request to Store', 'summary' => 'Ask the store for stock already on hand (coming next).'],
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
