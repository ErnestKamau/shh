<?php

$files = [
    'app/InventoryCategories.php',
    'app/InventoryDepartment.php',
    'app/InventoryItem.php',
    'app/InventoryItemNote.php',
    'app/InventoryLocation.php',
    'app/InventoryLocationUser.php',
    'app/InventoryOrder.php',
    'app/InventoryOrderItem.php',
    'app/InventoryOrderItemToInventoryItem.php',
    'app/InventoryStore.php',
    'app/InventoryStoreContact.php',
    'app/InventoryStoreSlot.php',
    'app/InventoryStoreSlotContent.php',
    'app/InventorySubCategories.php',
    'app/InventorySupplierRating.php',
    'app/InvoicableItem.php',
    'app/Invoice.php',
    'app/InvoiceDetails.php',
    'app/InvoicePaymentDetail.php',
    'app/ItemBrand.php',
    'app/ItemState.php',
    'app/JobDescription.php',
    'app/Lab.php',
    'app/LabCategoryItems.php',
    'app/LabInventoryCategory.php',
    'app/LabResultsExcel.php',
    'app/LabSectionApprover.php',
    'app/LabSectionApproverRelationShip.php',
    'app/LabStockMovement.php',
    'app/LabSubCategory.php',
    'app/MethodReagent.php',
    'app/MethodValidationRequest.php',
    'app/MyTestUsers.php',
    'app/NamingConvensionConsensus.php',
    'app/OTP.php',
    'app/PersonnelWorkHistory.php',
    'app/PhoneContact.php',
    'app/PricelistCustomer.php',
    'app/QuotationAttachment.php',
    'app/QuotationDetailAnalysisSplit.php',
    'app/QuotationDetails.php',
    'app/QuotationHeader.php',
    'app/QuotationHeaderView.php',
    'app/QuotationNotes.php',
    'app/RatingCriteria.php',
    'app/ReportFormat.php',
    'app/ReportFormatDetail.php',
    'app/ReportFormatSection.php',
    'app/ReportHeaderDetail.php',
    'app/ReportingUnit.php',
    'app/RequestEntity.php',
    'app/RequestEntityExtraCharge.php',
    'app/RequestEntityItem.php',
    'app/RequestType.php',
    'app/RequisitionLocation.php',
    'app/Result.php',
    'app/Role.php',
    'app/SampleAnalysisDates.php',
    'app/SampleAnalysisStage.php',
    'app/SampleAnalysisTypeRelation.php',
    'app/SampleAnalysisTypeRelationView.php',
    'app/SampleCondition.php',
    'app/SampleDate.php',
    'app/SampleDetails.php',
    'app/SampleHeader.php',
    'app/SampleImports.php',
    'app/SampleResults.php',
    'app/SamplesCategory.php',
    'app/SampleToSampleAnalysisStage.php',
    'app/SampleType.php',
    'app/SampleTypeCategory.php',
    'app/SavedReportConfiguration.php',
    'app/SchoolContacts.php',
    'app/StandardAnalytes.php',
    'app/Standards.php',
    'app/StandardValue.php',
    'app/StockTaking.php',
    'app/StockTakingCounter.php',
    'app/StockTakingSheet.php',
    'app/StockTransfer.php',
    'app/StockTransferItem.php',
    'app/StoreSlotInventorySubCategoryMapping.php',
    'app/StoreToCostCenter.php',
    'app/Supplier.php',
    'app/SupplierByCategory.php',
    'app/SupplierCategory.php',
    'app/SupplierContact.php',
    'app/SupplierContract.php',
    'app/SupplierContractItem.php',
    'app/SupplierQuote.php',
    'app/SupplierRFQ.php',
    'app/SupplierRatingCriteriaGuide.php',
    'app/SupplierRatingCriteriaGuideSupplierScore.php',
    'app/SuppliersRatingCriteria.php',
    'app/TaxRegime.php',
    'app/Topology.php',
    'app/UncertaintyBudget.php',
    'app/UncertaintySource.php',
    'app/UnitOfMeasureConversion.php',
    'app/UoMConversion.php',
    'app/User.php',
    'app/UserAlert.php',
    'app/UserDepartmentalApproval.php',
    'app/UserRole.php',
    'app/UserRoleView.php',
    'app/ViewRequestEntity.php',
    'app/ZohoApiTokens.php',
    'app/ZohoCustomers.php',
    'app/ZohoPricelist.php',
    'app/Zone.php',
];

$count = 0;
foreach ($files as $file) {
    if (!file_exists($file)) continue;

    $original = file_get_contents($file);
    $content = $original;

    // Apply HasUuids concern if neither string appears
    if (strpos($content, 'HasUuids') === false && strpos($content, 'extends Model') !== false) {
        $content = preg_replace('/use Illuminate\\\\Database\\\\Eloquent\\\\Model;/', "use Illuminate\\Database\\Eloquent\\Model;\nuse Illuminate\\Database\\Eloquent\\Concerns\\HasUuids;", $content);
        
        $replaced = preg_replace(
            '/class\s+([A-Za-z0-9_]+)\s+extends\s+Model\s*(implements\s+[A-Za-z0-9_,\s\n\\\\]+)?\s*\{/s', 
            "$0\n    use HasUuids;\n\n    protected \$keyType = 'string';\n    public \$incrementing = false;\n", 
            $content
        );
        
        if ($replaced !== null) {
            $content = $replaced;
        } else {
            echo "Regex error on $file\n";
        }
    }

    if ($original !== $content) {
        file_put_contents($file, $content);
        echo "Patched: $file\n";
        $count++;
    }
}
echo "Total patched: $count\n";
