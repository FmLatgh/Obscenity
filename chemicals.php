<?php
// Include the chemical database
require_once __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'chemicals.php';
// Time to make the actual website now, which includes the following:
// - An extensive search bar that allows you to search for a chemical by name, formula, state, or hazard (And has suggestions!)
// - You can also check if a chemical is safe to ingest
// - And as a bonus, you can make your own imaginary chemical and see if it's safe to ingest
// Time to get to work!
?>
<!DOCTYPE html>
<html>

<head>
    <title>Chemical Search</title>
    <meta charset="utf-8">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- jQuery (required by Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #e0f7fa;
            /* Light cyan background */
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            /* White background for container */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            /* Subtle shadow for depth */
            border-radius: 10px;
            /* Rounded corners */
        }

        h1 {
            text-align: center;
            color: #00796b;
            /* Teal color for heading */
        }

        form {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        input[type="text"] {
            width: 300px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #00796b;
            /* Teal border */
            border-radius: 5px;
        }

        input[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
            border: 1px solid #00796b;
            /* Teal border */
            border-radius: 5px;
            background-color: #00796b;
            /* Teal background */
            color: #ffffff;
            /* White text */
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #004d40;
            /* Darker teal on hover */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #00796b;
            /* Teal border */
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #00796b;
            /* Teal background for header */
            color: #ffffff;
            /* White text */
        }

        .suggestions {
            list-style-type: none;
            padding: 0;
            margin: 0;
            border: 1px solid #00796b;
            /* Teal border */
            max-height: 150px;
            overflow-y: auto;
            display: none;
            background-color: #ffffff;
            /* White background */
        }

        .suggestions li {
            padding: 10px;
            cursor: pointer;
        }

        .suggestions li:hover {
            background-color: #e0f7fa;
            /* Light cyan on hover */
        }

        #detailsModal {
            display: none;
            position: fixed;
            top: 55%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #ffffff;
            /* White background */
            padding: 20px;
            border: 1px solid #00796b;
            /* Teal border */
            border-radius: 10px;
            /* Rounded corners */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            /* Subtle shadow for depth */
        }

        #detailsModal h2 {
            color: #00796b;
            /* Teal color for heading */
        }

        #detailsModal button {
            padding: 10px 20px;
            font-size: 16px;
            border: 1px solid #00796b;
            /* Teal border */
            border-radius: 5px;
            background-color: #00796b;
            /* Teal background */
            color: #ffffff;
            /* White text */
            cursor: pointer;
        }

        #detailsModal button:hover {
            background-color: #004d40;
            /* Darker teal on hover */
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Chemical Search</h1>
        <form action="chemicalsearch.php" method="post">
            <select id="search" name="search" style="width: 100%;" placeholder="Search for a chemical...">
                <option value="">Type to search...</option>
            </select>
            <input type="submit" value="Search">
        </form>
        <?php
        // Check if the form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the search term
            $search = $_POST['search'];
            if (!empty($search)) {
                $results = search_chemicals($search);
                if (!empty($results)) {
                    echo '<table>';
                    echo '<tr><th>Name</th><th>Formula</th><th>State</th><th>Hazard</th><th>Safe to Ingest</th><th>Details</th></tr>';
                    foreach ($results as $result) {
                        $safeToIngest = method_exists($result, 'checkIfSafeToIngest') ? $result->checkIfSafeToIngest() : false;
                        echo '<tr>';
                        echo '<td>' . (get_class($result) === 'ChemicalStructure' ? 'Structure: ' : '') . $result->name . '</td>';
                        echo '<td>' . $result->chemicalFormula . '</td>';
                        echo '<td>' . $result->state . '</td>';
                        echo '<td>' . $result->dangerInfo->hazard . '</td>';
                        echo '<td>' . ($safeToIngest ? 'Yes' : 'No') . '</td>';
                        echo '<td><button type="button" onclick="viewDetails(\'' . $result->name . '\')">View Details</button></td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p>No results found.</p>';
                }
            } else {
                echo '<p>Please enter a search term.</p>';
            }
        }
        ?>
        <div id="detailsModal" style="display:none;">
            <div style="background-color: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">
                <h2>Chemical Details</h2>
                <p id="chemicalDetails"></p>
                <button onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>
    <script>
        function viewDetails(chemicalName) {
            const chemicals = <?php echo json_encode(array_map(function ($chemical) {
                                    return $chemical->getDetailedInfo();
                                }, $allChemicals)); ?>;
            const chemical = chemicals.find(c => c.name === chemicalName);
            if (chemical) {
                let details = `
                    Name: ${chemical.name}<br>
                    Formula: ${chemical.chemicalFormula}<br>
                    State: ${chemical.state}<br>
                    Hazard: ${chemical.hazard}<br>
                    Precaution: ${chemical.precaution}<br>
                    Safe to Ingest: ${chemical.safeToIngest ? 'Yes' : 'No'}<br>
                `;
                if (chemical.classType === 'Acid') {
                    details += `pH Level: ${chemical.phLevel}<br>`;
                } else if (chemical.classType === 'Base') {
                    details += `pH Level: ${chemical.phLevel}<br>`;
                } else if (chemical.classType === 'Solid') {
                    details += `Hardness: ${chemical.hardness}<br>`;
                } else if (chemical.classType === 'imaginaryChemical') {
                    details += `<p style="color: ${chemical.color}">Color: ${chemical.color}</p><br>`;
                }
                document.getElementById('chemicalDetails').innerHTML = details;
                document.getElementById('detailsModal').style.display = 'block';
            }
        }

        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        function matchStart(params, data) {
            // If there are no search terms, return all of the data
            if ($.trim(params.term) === '') {
                return data;
            }

            // Skip if there is no 'children' property
            if (typeof data.children === 'undefined') {
                return null;
            }

            // `data.children` contains the actual options that we are matching against
            var filteredChildren = [];
            $.each(data.children, function(idx, child) {
                if (child.text.toUpperCase().indexOf(params.term.toUpperCase()) == 0 || child.formula.toUpperCase().indexOf(params.term.toUpperCase()) == 0) {
                    filteredChildren.push(child);
                }
            });

            // If we matched any of the timezone group's children, then set the matched children on the group
            // and return the group object
            if (filteredChildren.length) {
                var modifiedData = $.extend({}, data, true);
                modifiedData.children = filteredChildren;

                // You can return modified objects from here
                // This includes matching the `children` how you want in nested data sets
                return modifiedData;
            }

            // Return `null` if the term should not be displayed
            return null;
        }

        $(document).ready(function() {
            $('#search').select2({
                placeholder: 'Search for a chemical...',
                minimumInputLength: 0,
                ajax: {
                    url: 'data/ac.php',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            query: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(chemical) {
                                return {
                                    id: chemical.id,
                                    text: chemical.text,
                                    formula: chemical.formula // Include formula in the data
                                };
                            })
                        };
                    },
                    cache: true
                },
                matcher: matchStart
            });

            $('#search').on('select2:select', function() {
                $('form').submit();
            });
        });
    </script>
</body>

</html>
 
<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'chemicals.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = isset($_POST['query']) ? $_POST['query'] : '';

    if (!empty($query)) {
        $results = search_chemicals($query);
        $response = [];

        foreach ($results as $result) {
            $response[] = [
                'id' => $result->name,
                'text' => (get_class($result) === 'ChemicalStructure' ? 'Structure: ' : '') . $result->name
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        echo json_encode([]);
    }
}
 
<?php

function b()
{
    echo "<br>";
}
function h()
{
    echo "<hr>";
}

class Chemical
{
    public string $name;
    public string $chemicalFormula;
    public string $state;
    public chemicalDangerInfo $dangerInfo;
    public static array $allChemicals = [];


    function __construct($name, $chemicalFormula, $state, chemicalDangerInfo $dangerInfo)
    {
        $this->name = $name;
        $this->chemicalFormula = $chemicalFormula;
        $this->state = $state;
        $this->dangerInfo = $dangerInfo;
        self::$allChemicals[] = $this;
    }

    function explainChemical()
    {
        echo $this->name . ' is a ' . $this->state . ' with a chemical formula of ' . $this->chemicalFormula . '.';
        echo "<br>";
        echo "Hazard: " . $this->dangerInfo->hazard . ". Precaution: " . $this->dangerInfo->precaution . ".";
    }

    function getChemicalFormula()
    {
        return $this->chemicalFormula;
    }

    function checkIfSafeToIngest()
    {
        $classType = get_class($this);
        $warningType = $this->dangerInfo->hazard;
        switch ($classType) {
            case 'Acid':
                return $this->phLevel < 7;
            case 'Base':
                return $this->phLevel > 7;
            case 'Solid':
            case 'Compound':
                return true;
            case 'imaginaryChemical':
                switch ($warningType) {
                    case 'Flammable':
                    case 'Corrosive':
                    case 'Toxic':
                    case 'Irritant':
                        return false;
                    default:
                        return true;
                }
            default:
                switch ($warningType) {
                    case 'Flammable':
                    case 'Corrosive':
                    case 'Toxic':
                    case 'Irritant':
                        return false;
                    default:
                        return true;
                }
        }
    }

    function getDetailedInfo()
    {
        return [
            'name' => $this->name,
            'chemicalFormula' => $this->chemicalFormula,
            'state' => $this->state,
            'hazard' => $this->dangerInfo->hazard,
            'precaution' => $this->dangerInfo->precaution,
            'safeToIngest' => $this->checkIfSafeToIngest(),
            'classType' => get_class($this),
            'phLevel' => $this->phLevel ?? null,
        ];
    }

    public static function getAllChemicals()
    {
        return self::$allChemicals;
    }
}

class Acid extends Chemical
{
    public string $phLevel;

    function __construct($name, $chemicalFormula, $state, $phLevel, chemicalDangerInfo $dangerInfo)
    {
        parent::__construct($name, $chemicalFormula, $state, $dangerInfo);
        $this->phLevel = $phLevel;
    }
}

class Base extends Chemical
{
    public string $phLevel;

    function __construct($name, $chemicalFormula, $state, $phLevel, chemicalDangerInfo $dangerInfo)
    {
        parent::__construct($name, $chemicalFormula, $state, $dangerInfo);
        $this->phLevel = $phLevel;
    }
}

class ChemicalStructure extends Chemical
{
    public int $toughness;
    public int $complexity;
    public string $structureType;

    function __construct($name, $chemicalFormula, $state, $toughness, $complexity, $structureType, chemicalDangerInfo $dangerInfo)
    {
        parent::__construct($name, $chemicalFormula, $state, $dangerInfo);
        $this->toughness = $toughness;
        $this->complexity = $complexity;
        $this->structureType = $structureType;
    }
}

class Solid extends Chemical
{
    public string $hardness;

    function __construct($name, $chemicalFormula, $state, $hardness, chemicalDangerInfo $dangerInfo)
    {
        parent::__construct($name, $chemicalFormula, $state, $dangerInfo);
        $this->hardness = $hardness;
    }
}

class Compound extends Chemical
{
    public string $compoundType;

    function __construct($name, $chemicalFormula, $state, $compoundType, chemicalDangerInfo $dangerInfo)
    {
        parent::__construct($name, $chemicalFormula, $state, $dangerInfo);
        $this->compoundType = $compoundType;
    }
}

class foodWithChemical
{
    public string $name;
    public array $chemicals;

    function __construct($name, array $chemicals)
    {
        $this->name = $name;
        $this->chemicals = $chemicals; // Ensure chemicals is an array of Chemical objects
    }

    function listChemicals()
    {
        echo $this->name . ' contains the following chemicals: <br>';
        b();
        foreach ($this->chemicals as $chemical) {
            echo "-";
            echo $chemical->explainChemical();
            b();
            b();
        }
    }
}

class imaginaryChemical extends Chemical
{
    public ChemicalStructure $structure;
    public string $color;

    function __construct($name, $chemicalFormula, $state, ChemicalStructure $structure, $color, chemicalDangerInfo $dangerInfo)
    {
        parent::__construct($name, $chemicalFormula, $state, $dangerInfo);
        $this->structure = $structure;
        $this->color = $color;
    }
}
class chemicalDangerInfo
{
    public string $hazard;
    public string $precaution;

    function __construct($hazard, $precaution)
    {
        $this->hazard = $hazard;
        $this->precaution = $precaution;
    }
}


// Define chemical danger information
$flammable = new chemicalDangerInfo(
    'Flammable',
    'Keep away from open flames, sparks, and heat sources. Store in a cool, well-ventilated area.'
);

$corrosive = new chemicalDangerInfo(
    'Corrosive',
    'Wear protective gloves, eye protection, and face shield. Avoid skin contact and inhalation. Handle with care.'
);

$toxic = new chemicalDangerInfo(
    'Toxic',
    'Avoid inhalation, ingestion, and skin contact. Use only in well-ventilated areas or under fume hood. Store securely away from food and beverages.'
);

$irritant = new chemicalDangerInfo(
    'Irritant',
    'Avoid direct contact with skin, eyes, and respiratory tract. Use protective gloves and safety goggles. In case of contact, rinse immediately with water.'
);

$radioactive = new chemicalDangerInfo(
    'Radioactive',
    'Minimize exposure time, increase distance, and use shielding. Handle with specialized equipment and protective clothing.'
);

$non_irritant = new chemicalDangerInfo(
    'Non-Irritant',
    'Generally safe to handle. However, follow standard laboratory safety procedures.'
);

$non_flammable = new chemicalDangerInfo(
    'Non-Flammable',
    'Not a fire hazard, but still store away from heat and oxidizers.'
);

$stimulant = new chemicalDangerInfo(
    'Stimulant',
    'Avoid excessive exposure. May cause increased heart rate, jitteriness, and hyperactivity. Use in controlled doses and under supervision.'
);

// Chemicals
$water = new Chemical('Water', 'H2O', 'Liquid', $irritant);
$capsaicin = new Chemical('Capsaicin', 'C18H27NO3', 'Solid', $irritant);
$jodium = new Chemical('Iodine', 'I2', 'Solid', $toxic);
$carbonDioxide = new Chemical('Carbon Dioxide', 'CO2', 'Gas', $irritant);
$ammonia = new Chemical('Ammonia', 'NH3', 'Gas', $corrosive);
$nitrogen = new Chemical('Nitrogen', 'N2', 'Gas', $non_irritant);
$hydrogen = new Chemical('Hydrogen', 'H2', 'Gas', $flammable);
$helium = new Chemical('Helium', 'He', 'Gas', $non_irritant);
$neon = new Chemical('Neon', 'Ne', 'Gas', $non_irritant);
$argon = new Chemical('Argon', 'Ar', 'Gas', $non_irritant);
$radon = new Chemical('Radon', 'Rn', 'Gas', $toxic);
$fluorine = new Chemical('Fluorine', 'F2', 'Gas', $toxic);
$chlorine = new Chemical('Chlorine', 'Cl2', 'Gas', $toxic);
$bromine = new Chemical('Bromine', 'Br2', 'Liquid', $toxic);
$potassium = new Chemical('Potassium', 'K', 'Solid', $flammable);
$calcium = new Chemical('Calcium', 'Ca', 'Solid', $irritant);
$theobromine = new Chemical('Theobromine', 'C7H8N4O2', 'Solid', $toxic);
$caffeine = new Chemical('Caffeine', 'C8H10N4O2', 'Solid', $stimulant);
$theine = new Chemical('Theine', 'C8H10N4O2', 'Solid', $stimulant);
$citricAcid = new Chemical('Citric Acid', 'C6H8O7', 'Solid', $irritant);
$ammonium = new Chemical('Ammonium', 'NH4', 'Solid', $corrosive);
$tartaricAcid = new Chemical('Tartaric Acid', 'C4H6O6', 'Solid', $irritant);

// Acids
$HydrochloricAcid = new Acid('Hydrochloric Acid', 'HCl', 'Liquid', '1', $corrosive);
$SulfuricAcid = new Acid('Sulfuric Acid', 'H2SO4', 'Liquid', '0', $corrosive);
$NitricAcid = new Acid('Nitric Acid', 'HNO3', 'Liquid', '0', $corrosive);
$AceticAcid = new Acid('Acetic Acid', 'CH3COOH', 'Liquid', '4', $irritant);
$PhosphoricAcid = new Acid('Phosphoric Acid', 'H3PO4', 'Liquid', '2', $irritant);
$CarbonicAcid = new Acid('Carbonic Acid', 'H2CO3', 'Aqueous', '6', $irritant);
$BoricAcid = new Acid('Boric Acid', 'H3BO3', 'Solid', '5', $irritant);
$HydrofluoricAcid = new Acid('Hydrofluoric Acid', 'HF', 'Liquid', '1', $corrosive);
$HydrobromicAcid = new Acid('Hydrobromic Acid', 'HBr', 'Liquid', '1', $corrosive);
$HydroiodicAcid = new Acid('Hydroiodic Acid', 'HI', 'Liquid', '1', $corrosive);
$PerchloricAcid = new Acid('Perchloric Acid', 'HClO4', 'Liquid', '0', $corrosive);
$SulfurousAcid = new Acid('Sulfurous Acid', 'H2SO3', 'Aqueous', '2', $corrosive);
$PhosphorousAcid = new Acid('Phosphorous Acid', 'H3PO3', 'Liquid', '1', $corrosive);


// Bases
$SodiumHydroxide = new Base('Sodium Hydroxide', 'NaOH', 'Solid', '14', $corrosive);
$PotassiumHydroxide = new Base('Potassium Hydroxide', 'KOH', 'Solid', '14', $corrosive);
$CalciumHydroxide = new Base('Calcium Hydroxide', 'Ca(OH)2', 'Solid', '12', $corrosive);
$AmmoniumHydroxide = new Base('Ammonium Hydroxide', 'NH4OH', 'Aqueous', '11', $corrosive);

// Solids
$Diamond = new Solid('Diamond', 'C', 'Solid', '10', $non_irritant);
$Graphite = new Solid('Graphite', 'C', 'Solid', '1', $non_irritant);
$Iron = new Solid('Iron', 'Fe', 'Solid', '5', $non_irritant);
$Copper = new Solid('Copper', 'Cu', 'Solid', '3', $non_irritant);
$Silver = new Solid('Silver', 'Ag', 'Solid', '4', $non_irritant);
$Gold = new Solid('Gold', 'Au', 'Solid', '5', $non_irritant);
$Platinum = new Solid('Platinum', 'Pt', 'Solid', '5', $non_irritant);
$Lead = new Solid('Lead', 'Pb', 'Solid', '0', $toxic);
$Mercury = new Chemical('Mercury', 'Hg', 'Liquid', $toxic); // Updated to level 0 for liquid
$Zinc = new Solid('Zinc', 'Zn', 'Solid', '2', $non_irritant);
$Nickel = new Solid('Nickel', 'Ni', 'Solid', '2', $irritant);
$Tin = new Solid('Tin', 'Sn', 'Solid', '2', $non_irritant);
$Uranium = new Solid('Uranium', 'U', 'Solid', '0', $radioactive);
$Plutonium = new Solid('Plutonium', 'Pu', 'Solid', '0', $radioactive);
$Thorium = new Solid('Thorium', 'Th', 'Solid', '0', $radioactive);
$Radium = new Solid('Radium', 'Ra', 'Solid', '0', $radioactive);
$Polonium = new Solid('Polonium', 'Po', 'Solid', '0', $radioactive);
$Bismuth = new Solid('Bismuth', 'Bi', 'Solid', '2', $non_irritant);

// Chemical Structures
$rockSolid = new ChemicalStructure('Rock Solid', 'RS', 'Solid', 10, 10, 'Cubic', $irritant);
$sphericallyCorrect = new ChemicalStructure('Spherically Correct', 'SC', 'Solid', 10, 10, 'Spherical', $irritant);
$viscous = new ChemicalStructure('Viscous', 'V', 'Liquid', 10, 10, 'Liquid', $irritant);
$gaseous = new ChemicalStructure('Gaseous', 'G', 'Gas', 10, 10, 'Gas', $irritant);

// Imaginary Chemicals
$newPartonium = new imaginaryChemical('New Partonium', 'Np', 'Solid', new ChemicalStructure('New Partonium', 'Np', 'Solid', 10, 10, 'Cubic', $irritant), 'Blue', $radioactive);
$rainbownium = new imaginaryChemical('Rainbownium', 'Rn', 'Liquid', new ChemicalStructure('Rainbownium', 'Rn', 'Liquid', 10, 10, 'Liquid', $irritant), 'Rainbow', $non_irritant);
$radiumChlorine = new imaginaryChemical('Radium Chlorine', 'RaCl', 'Solid', new ChemicalStructure('Radium Chlorine', 'RaCl', 'Solid', 10, 10, 'Cubic', $irritant), 'Green', $radioactive);
$pentofluoricAcidSalt = new imaginaryChemical('Pentofluoric Acid Salt', 'PFAS', 'Solid', new ChemicalStructure('Pentofluoric Acid Salt', 'PFAS', 'Solid', 10, 10, 'Cubic', $corrosive), 'White', $corrosive);
$gregor = new imaginaryChemical('Gregor', 'G', 'Solid', new ChemicalStructure('Gregor', 'G', 'Solid', 10, 10, 'Cubic', $non_irritant), 'Black', $non_irritant);
$tripleCapsaicin = new imaginaryChemical('Triple Capsaicin', 'C54H81NO9', 'Solid', new ChemicalStructure('Triple Capsaicin', 'C54H81NO9', 'Solid', 10, 10, 'Cubic', $irritant), 'Red', $irritant);
$hellFire = new imaginaryChemical('Hell Fire', 'HF', 'Gas', new ChemicalStructure('Hell Fire', 'HF', 'Gas', 10, 10, 'Gas', $flammable), 'Orange', $flammable);
$georgeMelonium = new imaginaryChemical('George Melonium', 'GM', 'Liquid', new ChemicalStructure('George Melonium', 'GM', 'Liquid', 10, 10, 'Liquid', $irritant), 'Yellow', $irritant);
$watermelonium = new imaginaryChemical('Watermelonium', 'WM', 'Liquid', new ChemicalStructure('Watermelonium', 'WM', 'Liquid', 10, 10, 'Liquid', $irritant), 'Pink', $irritant);
$chocolateCarbonadium = new imaginaryChemical('Chocolate Carbonadium', 'CC', 'Solid', new ChemicalStructure('Chocolate Carbonadium', 'CC', 'Solid', 10, 10, 'Cubic', $irritant), 'Brown', $irritant);
$theThingThatBurns = new imaginaryChemical('The Thing That Burns', 'TTTB', 'Gas', new ChemicalStructure('The Thing That Burns', 'TTTB', 'Gas', 10, 10, 'Gas', $flammable), 'Purple', $flammable);
$duoPerfluroricPenteticChronoAcid = new imaginaryChemical('Duo Perfluroric Pentetic Chrono Acid', 'DPPCA', 'Liquid', new ChemicalStructure('Duo Perfluroric Pentetic Chrono Acid', 'DPPCA', 'Liquid', 10, 10, 'Liquid', $corrosive), 'Grey', $corrosive);
// Food with Chemicals (if the food contains the chemical(s))
$pepper = new foodWithChemical('Pepper', [$capsaicin]);
$banana = new foodWithChemical('Banana', [$potassium]);
$apple = new foodWithChemical('Apple', [$calcium]);
$orange = new foodWithChemical('Orange', [$ammonium]);
$chocolate = new foodWithChemical('Chocolate', [$theobromine]);
$coffee = new foodWithChemical('Coffee', [$caffeine]);
$tea = new foodWithChemical('Tea', [$theine]);
$chili = new foodWithChemical('Chili', [$capsaicin]);
$lemon = new foodWithChemical('Lemon', [$citricAcid]);
$grape = new foodWithChemical('Grape', [$tartaricAcid]);
$vinegar = new foodWithChemical('Vinegar', [$AceticAcid]);


$allChemicals = Chemical::getAllChemicals();

// function to get search results, also returns partial matches, and case insensitive, returns all chemicals if search is empty
function search_chemicals($query)
{
    global $allChemicals;
    $results = [];
    foreach ($allChemicals as $chemical) {
        if (stripos($chemical->name, $query) !== false || stripos($chemical->chemicalFormula, $query) !== false) {
            $results[] = $chemical;
        }
    }
    return $results;
}
// Get all chemical names
function get_chemical_names()
{
    global $allChemicals;
    $names = [];
    foreach ($allChemicals as $chemical) {
        $names[] = $chemical->name;
    }
    return $names;
}
