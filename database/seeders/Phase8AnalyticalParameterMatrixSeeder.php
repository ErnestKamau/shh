<?php

namespace Database\Seeders;

use App\Analyte;
use App\AnalysisElements;
use App\AnalysisType;
use App\Company;
use App\Lab;
use App\SampleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Phase8AnalyticalParameterMatrixSeeder extends Seeder
{
    public const ANALYSIS_MATRIX = [
        // LAB-FCH (Forensic Chemistry Lab) - 10 parameters
        ['analysis_code' => 'ANA-CAN', 'analysis_name' => 'Cannabis Potency Screen', 'sample_code' => 'SMP-CAN', 'lab_code' => 'LAB-FCH', 'analyte_code' => 'ALY-THC', 'analyte_name' => 'Tetrahydrocannabinol (THC)', 'symbol' => 'THC', 'unit' => '% w/w', 'lod' => 0.05, 'hod' => 25.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-CTH', 'analysis_name' => 'Catha edulis Active Agent Assay', 'sample_code' => 'SMP-CAT', 'lab_code' => 'LAB-FCH', 'analyte_code' => 'ALY-CTH', 'analyte_name' => 'Cathinone Content', 'symbol' => 'Cathinone', 'unit' => '% w/w', 'lod' => 0.01, 'hod' => 5.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-COC', 'analysis_name' => 'Cocaine GC-MS Quantitation', 'sample_code' => 'SMP-COC', 'lab_code' => 'LAB-FCH', 'analyte_code' => 'ALY-COC', 'analyte_name' => 'Cocaine Hydrochloride', 'symbol' => 'Cocaine', 'unit' => '% w/w', 'lod' => 0.1, 'hod' => 90.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-AMP', 'analysis_name' => 'Amphetamine HPLC Check', 'sample_code' => 'SMP-AMP', 'lab_code' => 'LAB-FCH', 'analyte_code' => 'ALY-AMP', 'analyte_name' => 'Amphetamine Base', 'symbol' => 'Amphetamine', 'unit' => '% w/w', 'lod' => 0.1, 'hod' => 80.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-HER', 'analysis_name' => 'Heroin Purity GC-FID', 'sample_code' => 'SMP-HER', 'lab_code' => 'LAB-FCH', 'analyte_code' => 'ALY-HER', 'analyte_name' => 'Heroin Base', 'symbol' => 'Heroin', 'unit' => '% w/w', 'lod' => 0.1, 'hod' => 95.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-MET', 'analysis_name' => 'Methamphetamine Check', 'sample_code' => 'SMP-MET', 'lab_code' => 'LAB-FCH', 'analyte_code' => 'ALY-MET', 'analyte_name' => 'Methamphetamine HCl', 'symbol' => 'Meth', 'unit' => '% w/w', 'lod' => 0.1, 'hod' => 99.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-FEN', 'analysis_name' => 'Fentanyl Trace Assay', 'sample_code' => 'SMP-FEN', 'lab_code' => 'LAB-FCH', 'analyte_code' => 'ALY-FEN', 'analyte_name' => 'Fentanyl Trace', 'symbol' => 'Fentanyl', 'unit' => 'ppb', 'lod' => 0.01, 'hod' => 100.0, 'sig_figs' => 3, 'hours' => 24],
        ['analysis_code' => 'ANA-MOR', 'analysis_name' => 'Morphine Quantitative Assay', 'sample_code' => 'SMP-MCH', 'lab_code' => 'LAB-FCH', 'analyte_code' => 'ALY-MOR', 'analyte_name' => 'Morphine Content', 'symbol' => 'Morphine', 'unit' => '% w/w', 'lod' => 0.1, 'hod' => 50.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-COD', 'analysis_name' => 'Codeine HPLC Assay', 'sample_code' => 'SMP-MCH', 'lab_code' => 'LAB-FCH', 'analyte_code' => 'ALY-COD', 'analyte_name' => 'Codeine Content', 'symbol' => 'Codeine', 'unit' => '% w/w', 'lod' => 0.1, 'hod' => 60.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-DZP', 'analysis_name' => 'Diazepam Product Check', 'sample_code' => 'SMP-MCH', 'lab_code' => 'LAB-FCH', 'analyte_code' => 'ALY-DZP', 'analyte_name' => 'Diazepam Content', 'symbol' => 'Diazepam', 'unit' => '% w/w', 'lod' => 0.1, 'hod' => 99.0, 'sig_figs' => 2, 'hours' => 24],

        // LAB-FDNA (Forensic DNA Lab) - 10 parameters
        ['analysis_code' => 'ANA-STR', 'analysis_name' => 'STR DNA Profiling (Human)', 'sample_code' => 'SMP-RAP', 'lab_code' => 'LAB-FDNA', 'analyte_code' => 'ALY-STR', 'analyte_name' => 'Human STR DNA Profile Match', 'symbol' => 'STR-Match', 'unit' => '% Match', 'lod' => 50.0, 'hod' => 99.99, 'sig_figs' => 4, 'hours' => 48],
        ['analysis_code' => 'ANA-WLD', 'analysis_name' => 'Wildlife Species DNA ID', 'sample_code' => 'SMP-WLP', 'lab_code' => 'LAB-FDNA', 'analyte_code' => 'ALY-WDNA', 'analyte_name' => 'Wildlife DNA Species Similarity', 'symbol' => 'Wildlife-DNA', 'unit' => '% Similarity', 'lod' => 90.0, 'hod' => 99.99, 'sig_figs' => 4, 'hours' => 48],
        ['analysis_code' => 'ANA-YSTR', 'analysis_name' => 'Y-STR DNA Profiling', 'sample_code' => 'SMP-RAP', 'lab_code' => 'LAB-FDNA', 'analyte_code' => 'ALY-YSTR', 'analyte_name' => 'Y-STR Match Rate', 'symbol' => 'Y-STR', 'unit' => '% Match', 'lod' => 50.0, 'hod' => 99.99, 'sig_figs' => 4, 'hours' => 48],
        ['analysis_code' => 'ANA-MTDNA', 'analysis_name' => 'Mitochondrial DNA Match', 'sample_code' => 'SMP-MDN', 'lab_code' => 'LAB-FDNA', 'analyte_code' => 'ALY-MTDNA', 'analyte_name' => 'mtDNA Match Rate', 'symbol' => 'mtDNA', 'unit' => '% Match', 'lod' => 90.0, 'hod' => 99.99, 'sig_figs' => 4, 'hours' => 48],
        ['analysis_code' => 'ANA-ASTR', 'analysis_name' => 'Autosomal STR Match', 'sample_code' => 'SMP-RDN', 'lab_code' => 'LAB-FDNA', 'analyte_code' => 'ALY-ASTR', 'analyte_name' => 'Autosomal STR Match Rate', 'symbol' => 'aSTR', 'unit' => '% Match', 'lod' => 50.0, 'hod' => 99.99, 'sig_figs' => 4, 'hours' => 48],
        ['analysis_code' => 'ANA-DVI', 'analysis_name' => 'Disaster Victim Skeletal DNA ID', 'sample_code' => 'SMP-DVI', 'lab_code' => 'LAB-FDNA', 'analyte_code' => 'ALY-DVI', 'analyte_name' => 'Skeletal Match Rate', 'symbol' => 'DVI-Match', 'unit' => '% Match', 'lod' => 10.0, 'hod' => 99.99, 'sig_figs' => 4, 'hours' => 72],
        ['analysis_code' => 'ANA-WLTT', 'analysis_name' => 'Wildlife Trafficking DNA ID', 'sample_code' => 'SMP-WLT', 'lab_code' => 'LAB-FDNA', 'analyte_code' => 'ALY-WLT', 'analyte_name' => 'Species Match Rate', 'symbol' => 'WTraff-Match', 'unit' => '% Match', 'lod' => 80.0, 'hod' => 99.99, 'sig_figs' => 4, 'hours' => 48],
        ['analysis_code' => 'ANA-TDNA', 'analysis_name' => 'Touch DNA Amplification', 'sample_code' => 'SMP-MDN', 'lab_code' => 'LAB-FDNA', 'analyte_code' => 'ALY-TDNA', 'analyte_name' => 'DNA Concentration', 'symbol' => 'TouchDNA', 'unit' => 'ng/uL', 'lod' => 0.001, 'hod' => 10.0, 'sig_figs' => 4, 'hours' => 24],
        ['analysis_code' => 'ANA-HDNA', 'analysis_name' => 'Hair Shaft DNA Profiling', 'sample_code' => 'SMP-MDN', 'lab_code' => 'LAB-FDNA', 'analyte_code' => 'ALY-HDNA', 'analyte_name' => 'Hair DNA Similarity', 'symbol' => 'HairDNA', 'unit' => '% Match', 'lod' => 10.0, 'hod' => 99.99, 'sig_figs' => 4, 'hours' => 72],
        ['analysis_code' => 'ANA-SDNA', 'analysis_name' => 'Saliva Stain DNA ID', 'sample_code' => 'SMP-RAP', 'lab_code' => 'LAB-FDNA', 'analyte_code' => 'ALY-SDNA', 'analyte_name' => 'Saliva DNA Match', 'symbol' => 'SalivaDNA', 'unit' => '% Match', 'lod' => 50.0, 'hod' => 99.99, 'sig_figs' => 4, 'hours' => 48],

        // LAB-FTOX (Forensic Toxicology Lab) - 10 parameters
        ['analysis_code' => 'ANA-TOX', 'analysis_name' => 'Post-Mortem Poison Screen', 'sample_code' => 'SMP-MTO', 'lab_code' => 'LAB-FTOX', 'analyte_code' => 'ALY-CN', 'analyte_name' => 'Cyanide Concentration', 'symbol' => 'Cyanide', 'unit' => 'mg/L', 'lod' => 0.05, 'hod' => 10.0, 'sig_figs' => 3, 'hours' => 12],
        ['analysis_code' => 'ANA-ETH', 'analysis_name' => 'Ethanol Headspace GC-FID', 'sample_code' => 'SMP-FBL', 'lab_code' => 'LAB-FTOX', 'analyte_code' => 'ALY-ETH', 'analyte_name' => 'Blood Alcohol Concentration', 'symbol' => 'BAC', 'unit' => '% BAC', 'lod' => 0.005, 'hod' => 0.40, 'sig_figs' => 3, 'hours' => 12],
        ['analysis_code' => 'ANA-CO', 'analysis_name' => 'Blood Carbon Monoxide Screen', 'sample_code' => 'SMP-FBL', 'lab_code' => 'LAB-FTOX', 'analyte_code' => 'ALY-CO', 'analyte_name' => 'Carboxyhemoglobin Saturation', 'symbol' => 'COHb', 'unit' => '% HbCO', 'lod' => 0.1, 'hod' => 80.0, 'sig_figs' => 2, 'hours' => 12],
        ['analysis_code' => 'ANA-TOXP1', 'analysis_name' => 'Urine Drug Panel Screen', 'sample_code' => 'SMP-FUR', 'lab_code' => 'LAB-FTOX', 'analyte_code' => 'ALY-UDPS', 'analyte_name' => 'Opiate Screen', 'symbol' => 'UrineOpiates', 'unit' => 'ng/mL', 'lod' => 50.0, 'hod' => 2000.0, 'sig_figs' => 0, 'hours' => 24],
        ['analysis_code' => 'ANA-TOXP2', 'analysis_name' => 'Blood Morphine Quantitation', 'sample_code' => 'SMP-FBL', 'lab_code' => 'LAB-FTOX', 'analyte_code' => 'ALY-BMQ', 'analyte_name' => 'Free Morphine', 'symbol' => 'BloodMorphine', 'unit' => 'ng/mL', 'lod' => 5.0, 'hod' => 500.0, 'sig_figs' => 1, 'hours' => 24],
        ['analysis_code' => 'ANA-SAL', 'analysis_name' => 'Salicylate Toxicity Check', 'sample_code' => 'SMP-FUR', 'lab_code' => 'LAB-FTOX', 'analyte_code' => 'ALY-SAL', 'analyte_name' => 'Salicylate Level', 'symbol' => 'Salicylate', 'unit' => 'mg/L', 'lod' => 10.0, 'hod' => 800.0, 'sig_figs' => 1, 'hours' => 12],
        ['analysis_code' => 'ANA-METOH', 'analysis_name' => 'Methanol Post-Mortem Screen', 'sample_code' => 'SMP-MTO', 'lab_code' => 'LAB-FTOX', 'analyte_code' => 'ALY-METH', 'analyte_name' => 'Blood Methanol Level', 'symbol' => 'Methanol', 'unit' => 'mg/L', 'lod' => 5.0, 'hod' => 1000.0, 'sig_figs' => 1, 'hours' => 24],
        ['analysis_code' => 'ANA-PRQ', 'analysis_name' => 'Paraquat Poison Assay', 'sample_code' => 'SMP-AMTO', 'lab_code' => 'LAB-FTOX', 'analyte_code' => 'ALY-PARA', 'analyte_name' => 'Paraquat Concentration', 'symbol' => 'Paraquat', 'unit' => 'ug/mL', 'lod' => 0.01, 'hod' => 50.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-ARS', 'analysis_name' => 'Arsenic Spectroscopic Scan', 'sample_code' => 'SMP-MTO', 'lab_code' => 'LAB-FTOX', 'analyte_code' => 'ALY-AS', 'analyte_name' => 'Arsenic Level', 'symbol' => 'Arsenic', 'unit' => 'ug/L', 'lod' => 0.5, 'hod' => 500.0, 'sig_figs' => 1, 'hours' => 24],
        ['analysis_code' => 'ANA-PBB', 'analysis_name' => 'Lead Blood Panel', 'sample_code' => 'SMP-FBL', 'lab_code' => 'LAB-FTOX', 'analyte_code' => 'ALY-LEAD', 'analyte_name' => 'Blood Lead Level', 'symbol' => 'BloodLead', 'unit' => 'ug/dL', 'lod' => 0.1, 'hod' => 80.0, 'sig_figs' => 1, 'hours' => 24],

        // LAB-FD (Food and Drugs Lab) - 10 parameters
        ['analysis_code' => 'ANA-FOOD', 'analysis_name' => 'Food Contaminant Screen', 'sample_code' => 'SMP-FOOD', 'lab_code' => 'LAB-FD', 'analyte_code' => 'ALY-AFL', 'analyte_name' => 'Aflatoxin B1', 'symbol' => 'AFB1', 'unit' => 'ppb', 'lod' => 0.5, 'hod' => 20.0, 'sig_figs' => 2, 'hours' => 48],
        ['analysis_code' => 'ANA-DRUG', 'analysis_name' => 'Drug Product Assay', 'sample_code' => 'SMP-DRUG', 'lab_code' => 'LAB-FD', 'analyte_code' => 'ALY-API', 'analyte_name' => 'Active Pharmaceutical Ingredient', 'symbol' => 'API', 'unit' => '% label claim', 'lod' => 80.0, 'hod' => 120.0, 'sig_figs' => 2, 'hours' => 72],
        ['analysis_code' => 'ANA-PEST', 'analysis_name' => 'Pesticide Residue Analysis', 'sample_code' => 'SMP-FOOD', 'lab_code' => 'LAB-FD', 'analyte_code' => 'ALY-PEST', 'analyte_name' => 'Organophosphate Residue', 'symbol' => 'Pesticide', 'unit' => 'ppm', 'lod' => 0.01, 'hod' => 10.0, 'sig_figs' => 2, 'hours' => 48],
        ['analysis_code' => 'ANA-FHM', 'analysis_name' => 'Heavy Metals in Food', 'sample_code' => 'SMP-FOOD', 'lab_code' => 'LAB-FD', 'analyte_code' => 'ALY-FHM', 'analyte_name' => 'Cadmium Content', 'symbol' => 'Cadmium', 'unit' => 'mg/kg', 'lod' => 0.001, 'hod' => 5.0, 'sig_figs' => 3, 'hours' => 48],
        ['analysis_code' => 'ANA-FMC', 'analysis_name' => 'Food Moisture Content', 'sample_code' => 'SMP-FOOD', 'lab_code' => 'LAB-FD', 'analyte_code' => 'ALY-MOIS', 'analyte_name' => 'Moisture Percentage', 'symbol' => 'Moisture', 'unit' => '% w/w', 'lod' => 0.1, 'hod' => 95.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-PRES', 'analysis_name' => 'Preservative Quantitative Assay', 'sample_code' => 'SMP-FOOD', 'lab_code' => 'LAB-FD', 'analyte_code' => 'ALY-SO2', 'analyte_name' => 'Sulfur Dioxide Concentration', 'symbol' => 'SO2', 'unit' => 'mg/kg', 'lod' => 1.0, 'hod' => 2000.0, 'sig_figs' => 1, 'hours' => 48],
        ['analysis_code' => 'ANA-DIS', 'analysis_name' => 'Dissolution Rate Assay', 'sample_code' => 'SMP-DRUG', 'lab_code' => 'LAB-FD', 'analyte_code' => 'ALY-DIS', 'analyte_name' => 'Dissolution Percentage', 'symbol' => 'Dissolution', 'unit' => '% dissolved', 'lod' => 10.0, 'hod' => 100.0, 'sig_figs' => 1, 'hours' => 48],
        ['analysis_code' => 'ANA-UNI', 'analysis_name' => 'Pharmaceutical Uniformity of Mass', 'sample_code' => 'SMP-DRUG', 'lab_code' => 'LAB-FD', 'analyte_code' => 'ALY-UNI', 'analyte_name' => 'Mass Deviation', 'symbol' => 'MassDev', 'unit' => '% deviation', 'lod' => 0.0, 'hod' => 10.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-DPH', 'analysis_name' => 'Drug PH Level', 'sample_code' => 'SMP-DRUG', 'lab_code' => 'LAB-FD', 'analyte_code' => 'ALY-DPH', 'analyte_name' => 'pH Value', 'symbol' => 'DrugPH', 'unit' => 'pH units', 'lod' => 1.0, 'hod' => 14.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-EXC', 'analysis_name' => 'Excipient Identification', 'sample_code' => 'SMP-DRUG', 'lab_code' => 'LAB-FD', 'analyte_code' => 'ALY-EXC', 'analyte_name' => 'Excipient Purity', 'symbol' => 'Excipient', 'unit' => '% w/w', 'lod' => 10.0, 'hod' => 99.9, 'sig_figs' => 2, 'hours' => 48],

        // LAB-MIC (Microbiology Lab) - 10 parameters
        ['analysis_code' => 'ANA-MIC', 'analysis_name' => 'Total Viable Count', 'sample_code' => 'SMP-MIC', 'lab_code' => 'LAB-MIC', 'analyte_code' => 'ALY-TVC', 'analyte_name' => 'Total Viable Count', 'symbol' => 'TVC', 'unit' => 'CFU/g', 'lod' => 1.0, 'hod' => 100000.0, 'sig_figs' => 0, 'hours' => 72],
        ['analysis_code' => 'ANA-ECOLI', 'analysis_name' => 'Escherichia coli Count', 'sample_code' => 'SMP-MIC', 'lab_code' => 'LAB-MIC', 'analyte_code' => 'ALY-EC', 'analyte_name' => 'E. coli Count', 'symbol' => 'E.coli', 'unit' => 'CFU/g', 'lod' => 1.0, 'hod' => 10000.0, 'sig_figs' => 0, 'hours' => 48],
        ['analysis_code' => 'ANA-SALM', 'analysis_name' => 'Salmonella Detection', 'sample_code' => 'SMP-MIC', 'lab_code' => 'LAB-MIC', 'analyte_code' => 'ALY-SALM', 'analyte_name' => 'Salmonella Presence/Absence', 'symbol' => 'Salmonella', 'unit' => 'P/A', 'lod' => 0.0, 'hod' => 1.0, 'sig_figs' => 0, 'hours' => 72],
        ['analysis_code' => 'ANA-SAUR', 'analysis_name' => 'Staphylococcus aureus Screen', 'sample_code' => 'SMP-MIC', 'lab_code' => 'LAB-MIC', 'analyte_code' => 'ALY-SA', 'analyte_name' => 'Staph aureus Presence/Absence', 'symbol' => 'StaphAureus', 'unit' => 'P/A', 'lod' => 0.0, 'hod' => 1.0, 'sig_figs' => 0, 'hours' => 48],
        ['analysis_code' => 'ANA-YMC', 'analysis_name' => 'Yeast and Mold Count', 'sample_code' => 'SMP-MIC', 'lab_code' => 'LAB-MIC', 'analyte_code' => 'ALY-YM', 'analyte_name' => 'Yeast and Mold Count', 'symbol' => 'YeastMold', 'unit' => 'CFU/g', 'lod' => 1.0, 'hod' => 10000.0, 'sig_figs' => 0, 'hours' => 72],
        ['analysis_code' => 'ANA-LIST', 'analysis_name' => 'Listeria monocytogenes Check', 'sample_code' => 'SMP-MIC', 'lab_code' => 'LAB-MIC', 'analyte_code' => 'ALY-LIST', 'analyte_name' => 'Listeria Presence/Absence', 'symbol' => 'Listeria', 'unit' => 'P/A', 'lod' => 0.0, 'hod' => 1.0, 'sig_figs' => 0, 'hours' => 72],
        ['analysis_code' => 'ANA-BCER', 'analysis_name' => 'Bacillus cereus Assay', 'sample_code' => 'SMP-MIC', 'lab_code' => 'LAB-MIC', 'analyte_code' => 'ALY-BC', 'analyte_name' => 'Bacillus cereus Count', 'symbol' => 'B.cereus', 'unit' => 'CFU/g', 'lod' => 10.0, 'hod' => 10000.0, 'sig_figs' => 0, 'hours' => 48],
        ['analysis_code' => 'ANA-PSEU', 'analysis_name' => 'Pseudomonas aeruginosa Check', 'sample_code' => 'SMP-MIC', 'lab_code' => 'LAB-MIC', 'analyte_code' => 'ALY-PA', 'analyte_name' => 'Pseudomonas Presence/Absence', 'symbol' => 'Pseudomonas', 'unit' => 'P/A', 'lod' => 0.0, 'hod' => 1.0, 'sig_figs' => 0, 'hours' => 48],
        ['analysis_code' => 'ANA-COLI', 'analysis_name' => 'Total Coliforms Count', 'sample_code' => 'SMP-MIC', 'lab_code' => 'LAB-MIC', 'analyte_code' => 'ALY-TCC', 'analyte_name' => 'Total Coliform Count', 'symbol' => 'Coliforms', 'unit' => 'CFU/100mL', 'lod' => 1.0, 'hod' => 1000.0, 'sig_figs' => 0, 'hours' => 24],
        ['analysis_code' => 'ANA-ENT', 'analysis_name' => 'Enterobacteriaceae Count', 'sample_code' => 'SMP-MIC', 'lab_code' => 'LAB-MIC', 'analyte_code' => 'ALY-ENT', 'analyte_name' => 'Enterobacteriaceae Count', 'symbol' => 'Enterobacter', 'unit' => 'CFU/g', 'lod' => 1.0, 'hod' => 10000.0, 'sig_figs' => 0, 'hours' => 48],

        // LAB-ENV (Environmental Lab) - 10 parameters
        ['analysis_code' => 'ANA-WTR', 'analysis_name' => 'Water Quality Chemistry', 'sample_code' => 'SMP-WTR', 'lab_code' => 'LAB-ENV', 'analyte_code' => 'ALY-PH', 'analyte_name' => 'pH', 'symbol' => 'pH', 'unit' => 'pH units', 'lod' => 0.0, 'hod' => 14.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-SOIL', 'analysis_name' => 'Soil Heavy Metal Screen', 'sample_code' => 'SMP-SOIL', 'lab_code' => 'LAB-ENV', 'analyte_code' => 'ALY-PB', 'analyte_name' => 'Lead', 'symbol' => 'Pb', 'unit' => 'mg/kg', 'lod' => 0.1, 'hod' => 100.0, 'sig_figs' => 2, 'hours' => 72],
        ['analysis_code' => 'ANA-TURB', 'analysis_name' => 'Turbidity of Water', 'sample_code' => 'SMP-WTR', 'lab_code' => 'LAB-ENV', 'analyte_code' => 'ALY-TURB', 'analyte_name' => 'Water Turbidity', 'symbol' => 'Turbidity', 'unit' => 'NTU', 'lod' => 0.05, 'hod' => 50.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-DO', 'analysis_name' => 'Dissolved Oxygen Assay', 'sample_code' => 'SMP-WTR', 'lab_code' => 'LAB-ENV', 'analyte_code' => 'ALY-DO', 'analyte_name' => 'Dissolved Oxygen', 'symbol' => 'DO', 'unit' => 'mg/L', 'lod' => 0.1, 'hod' => 20.0, 'sig_figs' => 2, 'hours' => 12],
        ['analysis_code' => 'ANA-COD-ENV', 'analysis_name' => 'Chemical Oxygen Demand', 'sample_code' => 'SMP-WTR', 'lab_code' => 'LAB-ENV', 'analyte_code' => 'ALY-COD-ENV', 'analyte_name' => 'Chemical Oxygen Demand', 'symbol' => 'COD', 'unit' => 'mg/L', 'lod' => 5.0, 'hod' => 500.0, 'sig_figs' => 1, 'hours' => 24],
        ['analysis_code' => 'ANA-TDS', 'analysis_name' => 'Total Dissolved Solids', 'sample_code' => 'SMP-WTR', 'lab_code' => 'LAB-ENV', 'analyte_code' => 'ALY-TDS', 'analyte_name' => 'Total Dissolved Solids', 'symbol' => 'TDS', 'unit' => 'mg/L', 'lod' => 10.0, 'hod' => 2000.0, 'sig_figs' => 0, 'hours' => 24],
        ['analysis_code' => 'ANA-SOC', 'analysis_name' => 'Soil Organic Carbon', 'sample_code' => 'SMP-SOIL', 'lab_code' => 'LAB-ENV', 'analyte_code' => 'ALY-SOC', 'analyte_name' => 'Organic Carbon Content', 'symbol' => 'SOC', 'unit' => '% w/w', 'lod' => 0.01, 'hod' => 10.0, 'sig_figs' => 2, 'hours' => 72],
        ['analysis_code' => 'ANA-NIT', 'analysis_name' => 'Soil Nitrogen Level', 'sample_code' => 'SMP-SOIL', 'lab_code' => 'LAB-ENV', 'analyte_code' => 'ALY-NIT', 'analyte_name' => 'Nitrogen Level', 'symbol' => 'Nitrogen', 'unit' => 'mg/kg', 'lod' => 1.0, 'hod' => 5000.0, 'sig_figs' => 1, 'hours' => 48],
        ['analysis_code' => 'ANA-CAD-SOIL', 'analysis_name' => 'Cadmium in Soil', 'sample_code' => 'SMP-SOIL', 'lab_code' => 'LAB-ENV', 'analyte_code' => 'ALY-CD', 'analyte_name' => 'Cadmium Concentration', 'symbol' => 'Cd', 'unit' => 'mg/kg', 'lod' => 0.01, 'hod' => 50.0, 'sig_figs' => 2, 'hours' => 72],
        ['analysis_code' => 'ANA-HG-ENV', 'analysis_name' => 'Environmental Mercury Screen', 'sample_code' => 'SMP-SOIL', 'lab_code' => 'LAB-ENV', 'analyte_code' => 'ALY-HG', 'analyte_name' => 'Mercury Concentration', 'symbol' => 'Hg', 'unit' => 'mg/kg', 'lod' => 0.001, 'hod' => 10.0, 'sig_figs' => 3, 'hours' => 72],

        // LAB-TSU (Technical Service Unit Lab) - 10 parameters
        ['analysis_code' => 'ANA-TSU', 'analysis_name' => 'Instrument Calibration Verification', 'sample_code' => 'SMP-TSU', 'lab_code' => 'LAB-TSU', 'analyte_code' => 'ALY-CAL', 'analyte_name' => 'Calibration Deviation', 'symbol' => 'CAL-DEV', 'unit' => '% deviation', 'lod' => 0.0, 'hod' => 5.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-CAL-TEMP', 'analysis_name' => 'Temperature Sensors Calibration', 'sample_code' => 'SMP-TSU', 'lab_code' => 'LAB-TSU', 'analyte_code' => 'ALY-CTEMP', 'analyte_name' => 'Temperature Deviation', 'symbol' => 'TempDev', 'unit' => 'deg C deviation', 'lod' => 0.0, 'hod' => 2.0, 'sig_figs' => 3, 'hours' => 24],
        ['analysis_code' => 'ANA-CAL-BAL', 'analysis_name' => 'Balance Calibration Verification', 'sample_code' => 'SMP-TSU', 'lab_code' => 'LAB-TSU', 'analyte_code' => 'ALY-CBAL', 'analyte_name' => 'Mass Deviation', 'symbol' => 'MassDev', 'unit' => 'mg deviation', 'lod' => 0.0, 'hod' => 10.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-CAL-PIP', 'analysis_name' => 'Pipette Volume Calibration', 'sample_code' => 'SMP-TSU', 'lab_code' => 'LAB-TSU', 'analyte_code' => 'ALY-CPIP', 'analyte_name' => 'Volume Deviation', 'symbol' => 'VolDev', 'unit' => 'uL deviation', 'lod' => 0.0, 'hod' => 5.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-CAL-SPEC', 'analysis_name' => 'Spectrophotometer Wavelength Calibration', 'sample_code' => 'SMP-TSU', 'lab_code' => 'LAB-TSU', 'analyte_code' => 'ALY-CSPEC', 'analyte_name' => 'Wavelength Shift', 'symbol' => 'WavelengthDev', 'unit' => 'nm shift', 'lod' => 0.0, 'hod' => 5.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-CAL-PH', 'analysis_name' => 'pH Meter Buffer Calibration', 'sample_code' => 'SMP-TSU', 'lab_code' => 'LAB-TSU', 'analyte_code' => 'ALY-CPH', 'analyte_name' => 'pH Buffer Deviation', 'symbol' => 'pHBufferDev', 'unit' => 'pH deviation', 'lod' => 0.0, 'hod' => 0.20, 'sig_figs' => 3, 'hours' => 24],
        ['analysis_code' => 'ANA-CAL-PRES', 'analysis_name' => 'Autoclave Pressure Calibration', 'sample_code' => 'SMP-TSU', 'lab_code' => 'LAB-TSU', 'analyte_code' => 'ALY-CPRES', 'analyte_name' => 'Pressure Deviation', 'symbol' => 'PresDev', 'unit' => 'psi deviation', 'lod' => 0.0, 'hod' => 5.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-CAL-HUM', 'analysis_name' => 'Incubator Humidity Verification', 'sample_code' => 'SMP-TSU', 'lab_code' => 'LAB-TSU', 'analyte_code' => 'ALY-CHUM', 'analyte_name' => 'Humidity Deviation', 'symbol' => 'HumDev', 'unit' => '%RH deviation', 'lod' => 0.0, 'hod' => 5.0, 'sig_figs' => 2, 'hours' => 24],
        ['analysis_code' => 'ANA-CAL-SPD', 'analysis_name' => 'Centrifuge Speed Calibration', 'sample_code' => 'SMP-TSU', 'lab_code' => 'LAB-TSU', 'analyte_code' => 'ALY-CSPD', 'analyte_name' => 'RPM Deviation', 'symbol' => 'RPMDev', 'unit' => 'RPM deviation', 'lod' => 0.0, 'hod' => 50.0, 'sig_figs' => 0, 'hours' => 24],
        ['analysis_code' => 'ANA-CAL-FLOW', 'analysis_name' => 'Chromatograph Flow Rate Calibration', 'sample_code' => 'SMP-TSU', 'lab_code' => 'LAB-TSU', 'analyte_code' => 'ALY-CFLOW', 'analyte_name' => 'Flow Rate Deviation', 'symbol' => 'FlowDev', 'unit' => 'mL/min deviation', 'lod' => 0.0, 'hod' => 0.10, 'sig_figs' => 3, 'hours' => 24],
    ];

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 8 SEEDING: Analytical Parameter Matrix');
            $this->command?->info('====================================================');

            $company = Company::query()->first();
            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');
                return;
            }

            $sampleTypes = SampleType::query()->where('company_id', $company->id)->get()->keyBy('code');

            foreach (self::ANALYSIS_MATRIX as $row) {
                $sampleType = $sampleTypes->get($row['sample_code']);

                if (! $sampleType) {
                    $this->command?->warning("Skipped {$row['analysis_code']}: missing sample type.");
                    continue;
                }

                $analyte = Analyte::query()->updateOrCreate(
                    ['code' => $row['analyte_code'], 'company_id' => $company->id],
                    [
                        'name' => $row['analyte_name'],
                        'decimal_places' => (int) $row['sig_figs'],
                        'reporting_symbol' => $row['symbol'],
                        'reporting_unit' => $row['unit'],
                        'method' => $row['analysis_name'],
                        'common_name' => $row['analyte_name'],
                        'non_detectable' => false,
                        'non_accredited' => false,
                        'active' => true,
                        'show_on_report' => true,
                    ]
                );

                // Fetch all matching laboratory instances across all zones
                $matchingLabs = Lab::query()
                    ->where('company_id', $company->id)
                    ->where('code', 'like', $row['lab_code'] . '-%')
                    ->get();

                if ($matchingLabs->isEmpty()) {
                    $this->command?->warning("No matching physical labs found for code: {$row['lab_code']}");
                    continue;
                }

                foreach ($matchingLabs as $lab) {
                    $uniqueAnalysisCode = $row['analysis_code'] . '-' . $lab->zone->key;

                    $analysisType = AnalysisType::query()->updateOrCreate(
                        ['code' => $uniqueAnalysisCode, 'company_id' => $company->id],
                        [
                            'name' => $row['analysis_name'] . ' (' . $lab->zone->value . ')',
                            'description' => $row['analysis_name'] . ' procedure run at ' . $lab->name,
                            'sample_type_id' => $sampleType->id,
                            'lab_id' => $lab->id,
                            'active' => true,
                            'has_no_result' => false,
                            'level' => 1,
                            'reporting_time' => $row['hours'],
                            'short_name' => $lab->code,
                        ]
                    );

                    $analysisType->labs()->syncWithoutDetaching([$lab->id]);

                    AnalysisElements::query()->updateOrCreate(
                        [
                            'analysis_type_id' => $analysisType->id,
                            'analyte_id' => $analyte->id,
                        ],
                        [
                            'decimal_places' => (int) $row['sig_figs'],
                            'reporting_symbol' => $row['symbol'],
                            'reporting_unit' => $row['unit'],
                            'active' => true,
                            'company_id' => $company->id,
                            'show_on_report' => true,
                            'is_manual' => 0,
                            'significant_figures' => $row['sig_figs'],
                            'lod' => $row['lod'],
                            'hod' => $row['hod'],
                            'level' => 1,
                            'reporting_time' => $row['hours'],
                            'result_is_calculated' => false,
                            'recommend_remedies' => false,
                            'has_method_sequence' => false,
                        ]
                    );

                    $this->command?->info("Seeded Parameter: {$uniqueAnalysisCode} / {$row['analyte_code']} ({$lab->code})");
                }
            }

            $this->ensureMinimumParametersPerAnalysis($company);

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 8 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }

    private function ensureMinimumParametersPerAnalysis(Company $company): void
    {
        $analysisTypes = AnalysisType::query()
            ->where('company_id', $company->id)
            ->where('active', true)
            ->get();

        foreach ($analysisTypes as $analysisType) {
            $existingAnalyteIds = AnalysisElements::query()
                ->where('analysis_type_id', $analysisType->id)
                ->pluck('analyte_id')
                ->all();

            if (count($existingAnalyteIds) >= 2) {
                continue;
            }

            $needed = 2 - count($existingAnalyteIds);
            $candidateQuery = AnalysisElements::query()
                ->join('analysis_types', 'analysis_types.id', '=', 'analysis_elements.analysis_type_id')
                ->where('analysis_types.company_id', $company->id)
                ->where('analysis_types.sample_type_id', $analysisType->sample_type_id)
                ->whereNotIn('analysis_elements.analyte_id', $existingAnalyteIds)
                ->select('analysis_elements.*')
                ->limit(max(5, $needed * 5));

            $candidates = $candidateQuery->get()
                ->unique('analyte_id')
                ->take($needed)
                ->values();

            if ($candidates->count() < $needed) {
                $fallbacks = AnalysisElements::query()
                    ->where('company_id', $company->id)
                    ->whereNotIn('analyte_id', array_merge($existingAnalyteIds, $candidates->pluck('analyte_id')->all()))
                    ->limit($needed - $candidates->count())
                    ->get();

                $candidates = $candidates->merge($fallbacks);
            }

            foreach ($candidates as $candidate) {
                AnalysisElements::query()->updateOrCreate(
                    [
                        'analysis_type_id' => $analysisType->id,
                        'analyte_id' => $candidate->analyte_id,
                    ],
                    [
                        'decimal_places' => $candidate->decimal_places,
                        'reporting_symbol' => $candidate->reporting_symbol,
                        'reporting_unit' => $candidate->reporting_unit,
                        'active' => true,
                        'company_id' => $company->id,
                        'show_on_report' => true,
                        'is_manual' => 0,
                        'significant_figures' => $candidate->significant_figures,
                        'lod' => $candidate->lod,
                        'hod' => $candidate->hod,
                        'level' => $candidate->level,
                        'reporting_time' => $candidate->reporting_time ?: $analysisType->reporting_time,
                        'result_is_calculated' => false,
                        'recommend_remedies' => false,
                        'has_method_sequence' => false,
                    ]
                );
            }

            if ($candidates->isNotEmpty()) {
                $this->command?->info("Ensured {$analysisType->code} has at least two seeded parameters.");
            }
        }
    }
}
