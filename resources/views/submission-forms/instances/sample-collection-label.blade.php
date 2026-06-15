<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Collection Label</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background: #fff;
        }
        .label-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        .collection-section {
            margin-bottom: 20px;
        }
        .field {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .field-label {
            font-weight: bold;
            width: 200px;
            flex-shrink: 0;
        }
        .field-value {
            flex-grow: 1;
            border-bottom: 1px solid #000;
            padding: 2px 5px;
        }
        .checkbox-group {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .registration-section {
            border: 2px solid #000;
            padding: 15px;
            margin-top: 20px;
        }
        .registration-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }
        .registration-field {
            margin-bottom: 15px;
        }
        .registration-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .registration-value {
            border: 1px solid #000;
            padding: 8px;
            min-height: 30px;
            font-weight: bold;
            text-align: center;
        }
        .divider {
            border-bottom: 1px solid #000;
            margin: 10px 0;
        }
        .print-btn {
            margin: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">Print Label</button>
    
    <div class="label-container">
        <div class="header">
            @if($logoPath)
                <img src="{{ asset($logoPath) }}" alt="Company Logo" style="max-width: 100px; max-height: 50px; margin-bottom: 10px;">
            @endif
            <h1>SAMPLE COLLECTION LABEL</h1>
        </div>

        <div class="collection-section">
            <div class="field">
                <div class="field-label">Sample Name / Description:</div>
                <div class="field-value">{{ $sampleName }}</div>
            </div>
            <div class="field">
                <div class="field-label">Batch Number:</div>
                <div class="field-value">{{ $batchNumber }}</div>
            </div>
            <div class="field">
                <div class="field-label">Client Name:</div>
                <div class="field-value">{{ $clientName }}</div>
            </div>
            <div class="field">
                <div class="field-label">Site – Location:</div>
                <div class="field-value">{{ $siteLocation }}</div>
            </div>
            <div class="field">
                <div class="field-label">Date & Time of Collection:</div>
                <div class="field-value">{{ $dateTimeOfCollection }}</div>
            </div>
            <div class="field">
                <div class="field-label">Sample Temperature (°C):</div>
                <div class="field-value">{{ $sampleTemperature }}</div>
            </div>
            <div class="field">
                <div class="field-label">Collected By (Name / Sign):</div>
                <div class="field-value">{{ $collectedBy }}</div>
            </div>
            <div class="field">
                <div class="field-label">Preservation Applied:</div>
                <div class="field-value">
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" {{ $preservationApplied === 'Yes' ? 'checked' : '' }}> Yes
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" {{ $preservationApplied === 'No' ? 'checked' : '' }}> No
                        </div>
                        <div class="checkbox-item">
                            (Specify: ____________)
                        </div>
                    </div>
                </div>
            </div>
            <div class="field">
                <div class="field-label">Container Type:</div>
                <div class="field-value">
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" {{ $containerType === 'HDPE' ? 'checked' : '' }}> HDPE
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" {{ $containerType === 'Glass Bottle' ? 'checked' : '' }}> Glass Bottle
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" {{ $containerType === 'Zipper Bag' ? 'checked' : '' }}> Zipper Bag
                        </div>
                    </div>
                </div>
            </div>
            <div class="field">
                <div class="field-label">Sample Collection for:</div>
                <div class="field-value">
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" {{ $sampleCollectionFor === 'Micro Lab' ? 'checked' : '' }}> Micro Lab
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" {{ $sampleCollectionFor === 'Chemistry Lab' ? 'checked' : '' }}> Chemistry Lab
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="registration-section">
            <div class="registration-header">Registration Label with barcode</div>
            
            <div class="registration-field">
                <div class="registration-label">Job ID</div>
                <div class="divider"></div>
                <div class="registration-value">{{ $jobNumber }}</div>
            </div>
            
            <div class="registration-field">
                <div class="registration-label">Sample ID</div>
                <div class="divider"></div>
                <div class="registration-value">{{ $sampleId }}</div>
            </div>
            
            <div class="registration-field">
                <div class="registration-label">Sample Description</div>
                <div class="divider"></div>
                <div class="registration-value">{{ $sampleDescription }}</div>
            </div>
            
            <div class="registration-field">
                <div class="registration-label">Date & Time of Collection:</div>
                <div class="divider"></div>
                <div class="registration-value">{{ $dateTimeOfCollection }}</div>
            </div>
            
            <div class="registration-field">
                <div class="registration-label">Test Requirement</div>
                <div class="divider"></div>
                <div class="registration-value">{{ $testRequirement }}</div>
            </div>
        </div>
    </div>
</body>
</html>
