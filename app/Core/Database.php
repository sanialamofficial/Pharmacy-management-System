<?php
declare(strict_types=1);

namespace App\Core;

class Database
{
    private static ?\PDO $instance = null;

    public static function getInstance(): \PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/config.php';
            $dbPath = $config['db_path'];
            $needsSeed = !file_exists($dbPath);

            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $pdo = new \PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $pdo->exec('PRAGMA foreign_keys = ON;');

            if ($needsSeed) {
                $schema = file_get_contents($config['schema_path']);
                $pdo->exec($schema);
                self::seed($pdo);
            }

            self::$instance = $pdo;
        }

        return self::$instance;
    }

    private static function seed(\PDO $pdo): void
    {
        $passwordHash = password_hash('greenleaf', PASSWORD_DEFAULT);

        // 1. Seed Users (Admin + 4 Staff matching reference site)
        $users = [
            ['Rehana Kabir', 'rehana@greenleaf.rx', $passwordHash, 'admin', 'Lead Pharmacist & Owner', 'Day (9a-6p)', '+1 (555) 210-4401', 'active', date('Y-m-d', strtotime('-420 days'))],
            ['Tomás Okafor', 'tomas@greenleaf.rx', $passwordHash, 'staff', 'Senior Counter Pharmacist', 'Morning (8a-4p)', '+1 (555) 210-4402', 'active', date('Y-m-d', strtotime('-290 days'))],
            ['Priya Venkataraman', 'priya@greenleaf.rx', $passwordHash, 'staff', 'Counter Pharmacist', 'Morning (8a-4p)', '+1 (555) 210-4403', 'active', date('Y-m-d', strtotime('-215 days'))],
            ['Elin Marsh', 'elin@greenleaf.rx', $passwordHash, 'staff', 'Pharmacy Dispenser', 'Evening (1p-9p)', '+1 (555) 210-4404', 'active', date('Y-m-d', strtotime('-140 days'))],
            ['Dawit Haile', 'dawit@greenleaf.rx', $passwordHash, 'staff', 'Pharmacy Technician', 'Evening (1p-9p)', '+1 (555) 210-4405', 'suspended', date('Y-m-d', strtotime('-310 days'))],
        ];

        $userStmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role, job_title, shift, phone, status, joined_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($users as $u) {
            $userStmt->execute($u);
        }

        // 2. Seed 32 Medicines across all 11 categories
        $medicines = [
            // Low stock (< 5) & Expiring soon items
            ['ORSaline-N', 'Oral Rehydration Salts', '10.25 g', 'Sachet', 'Gastrointestinal', 'SMC Enterprise', 'Shelf C3', 0.45, 0, 'OS2411', date('Y-m-d', strtotime('+240 days')), 0],
            ['Alatrol', 'Cetirizine Hydrochloride', '10 mg', 'Tablet', 'Allergy', 'Square Pharma', 'Shelf E1', 0.35, 1, 'AL2488', date('Y-m-d', strtotime('+190 days')), 0],
            ['Mixtard 30', 'Human Insulin (30/70)', '100 IU/ml', 'Vial', 'Diabetes', 'Novo Nordisk', 'Shelf Fridge', 16.72, 2, 'MX2404', date('Y-m-d', strtotime('+140 days')), 1],
            ['Brufen', 'Ibuprofen', '400 mg', 'Tablet', 'Analgesic', 'Abbott', 'Shelf A2', 0.60, 3, 'BR2419', date('Y-m-d', strtotime('+280 days')), 0],
            ['Zimax', 'Azithromycin', '500 mg', 'Tablet', 'Antibiotic', 'Square Pharma', 'Shelf B1', 1.85, 4, 'ZM2433', date('Y-m-d', strtotime('+210 days')), 1],

            // Expiring soon (<= 30 days)
            ['Rivotril', 'Clonazepam', '0.5 mg', 'Tablet', 'Neurology & Mental Health', 'Roche', 'Shelf N1', 1.20, 28, 'RV2391', date('Y-m-d', strtotime('+11 days')), 1],
            ['Ventolin', 'Salbutamol', '100 mcg', 'Inhaler', 'Respiratory', 'GSK', 'Shelf R2', 6.40, 18, 'VN2377', date('Y-m-d', strtotime('+17 days')), 1],
            ['Antacid Plus', 'Magaldrate + Simethicone', '200 ml', 'Suspension', 'Gastrointestinal', 'Square Pharma', 'Shelf C1', 3.25, 22, 'OP2469', date('Y-m-d', strtotime('+26 days')), 0],

            // Regular catalog (24 more = 32 total)
            ['Azithrocin', 'Azithromycin', '500 mg', 'Tablet', 'Antibiotic', 'Beximco Pharma', 'Shelf B1', 1.75, 42, 'AZ2451', date('Y-m-d', strtotime('+310 days')), 1],
            ['Ciprocin', 'Ciprofloxacin', '500 mg', 'Tablet', 'Antibiotic', 'Square Pharma', 'Shelf B2', 2.45, 64, 'CP2490', date('Y-m-d', strtotime('+340 days')), 1],
            ['Fexo', 'Fexofenadine', '120 mg', 'Tablet', 'Allergy', 'Square Pharma', 'Shelf E2', 0.90, 58, 'FX2462', date('Y-m-d', strtotime('+265 days')), 0],
            ['Napa Extra', 'Paracetamol + Caffeine', '500 mg', 'Tablet', 'Analgesic', 'Beximco Pharma', 'Shelf A1', 0.28, 140, 'NP2499', date('Y-m-d', strtotime('+420 days')), 0],
            ['Ace Plus', 'Paracetamol + Caffeine', '500 mg', 'Tablet', 'Analgesic', 'Square Pharma', 'Shelf A1', 0.26, 95, 'AC2471', date('Y-m-d', strtotime('+390 days')), 0],
            ['Seclo', 'Omeprazole', '20 mg', 'Capsule', 'Gastrointestinal', 'Square Pharma', 'Shelf C2', 0.55, 86, 'SC2484', date('Y-m-d', strtotime('+365 days')), 0],
            ['Sergel', 'Esomeprazole', '20 mg', 'Capsule', 'Gastrointestinal', 'Healthcare Pharma', 'Shelf C2', 0.65, 74, 'SG2455', date('Y-m-d', strtotime('+295 days')), 0],
            ['Amdocal', 'Amlodipine', '5 mg', 'Tablet', 'Cardiovascular', 'Beximco Pharma', 'Shelf D1', 0.70, 52, 'AM2440', date('Y-m-d', strtotime('+330 days')), 1],
            ['Bizoran', 'Amlodipine + Olmesartan', '5/20 mg', 'Tablet', 'Cardiovascular', 'Beximco Pharma', 'Shelf D1', 1.95, 36, 'BZ2438', date('Y-m-d', strtotime('+250 days')), 1],
            ['Atova', 'Atorvastatin', '20 mg', 'Tablet', 'Cardiovascular', 'Square Pharma', 'Shelf D2', 1.40, 48, 'AT2422', date('Y-m-d', strtotime('+305 days')), 1],
            ['Comet', 'Metformin Hydrochloride', '500 mg', 'Tablet', 'Diabetes', 'Square Pharma', 'Shelf D3', 0.40, 110, 'CM2466', date('Y-m-d', strtotime('+400 days')), 1],
            ['Jardiance', 'Empagliflozin', '10 mg', 'Tablet', 'Diabetes', 'Boehringer Ingelheim', 'Shelf D3', 4.80, 24, 'JD2415', date('Y-m-d', strtotime('+275 days')), 1],
            ['Monas', 'Montelukast', '10 mg', 'Tablet', 'Respiratory', 'ACME Lab', 'Shelf R1', 1.60, 44, 'MN2459', date('Y-m-d', strtotime('+320 days')), 1],
            ['Tofen', 'Ketotifen', '1 mg', 'Tablet', 'Respiratory', 'Beximco Pharma', 'Shelf R1', 0.50, 38, 'TF2429', date('Y-m-d', strtotime('+245 days')), 0],
            ['Moxacil', 'Amoxicillin', '500 mg', 'Capsule', 'Antibiotic', 'Square Pharma', 'Shelf B3', 0.85, 62, 'MX2478', date('Y-m-d', strtotime('+350 days')), 1],
            ['Cef-3', 'Cefixime', '200 mg', 'Capsule', 'Antibiotic', 'Square Pharma', 'Shelf B3', 2.90, 34, 'CF2443', date('Y-m-d', strtotime('+285 days')), 1],
            ['Betnovate-N', 'Betamethasone + Neomycin', '0.1%', 'Cream', 'Dermatology', 'GSK', 'Shelf F1', 2.15, 29, 'BN2431', date('Y-m-d', strtotime('+220 days')), 1],
            ['Fungidal', 'Miconazole Nitrate', '2%', 'Cream', 'Dermatology', 'Square Pharma', 'Shelf F1', 1.45, 31, 'FG2412', date('Y-m-d', strtotime('+260 days')), 0],
            ['Tears Naturale', 'Dextran 70 + Hypromellose', '15 ml', 'Drops', 'Eye & Ear', 'Alcon', 'Shelf G1', 5.20, 19, 'TN2408', date('Y-m-d', strtotime('+195 days')), 0],
            ['Cipro-D', 'Ciprofloxacin + Dexamethasone', '10 ml', 'Drops', 'Eye & Ear', 'Square Pharma', 'Shelf G1', 2.75, 25, 'CD2460', date('Y-m-d', strtotime('+230 days')), 1],
            ['Nexum', 'Esomeprazole', '40 mg', 'Capsule', 'Gastrointestinal', 'Renata Ltd', 'Shelf C2', 0.95, 50, 'NX2481', date('Y-m-d', strtotime('+345 days')), 0],
            ['Neuro-B', 'Vitamin B1 + B6 + B12', '100/200 mg', 'Tablet', 'Vitamins & Supplements', 'Square Pharma', 'Shelf V1', 0.65, 84, 'NB2493', date('Y-m-d', strtotime('+410 days')), 0],
            ['Calbo-D', 'Calcium + Vitamin D3', '500 mg', 'Tablet', 'Vitamins & Supplements', 'Square Pharma', 'Shelf V1', 0.80, 72, 'CB2474', date('Y-m-d', strtotime('+380 days')), 0],
            ['D-Rise', 'Cholecalciferol', '20000 IU', 'Capsule', 'Vitamins & Supplements', 'Beximco Pharma', 'Shelf V2', 1.35, 45, 'DR2452', date('Y-m-d', strtotime('+315 days')), 0],
        ];

        $medStmt = $pdo->prepare('INSERT INTO medicines (brand_name, generic_name, strength, form, category, manufacturer, shelf_location, unit_price, stock_qty, batch_no, expiry_date, rx_required) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($medicines as $m) {
            $medStmt->execute($m);
        }

        // 3. Seed realistic sales over the last 30 days
        $medRows = $pdo->query('SELECT id, brand_name, strength, unit_price, rx_required FROM medicines')->fetchAll();
        $staffIds = [2, 3, 4]; // Tomás, Priya, Elin
        $payments = ['Cash', 'Card', 'Mobile'];
        $customers = [
            ['Amina Rahman', '+1 (555) 401-1201'],
            ['David Chen', '+1 (555) 401-2204'],
            ['Nadia Begum', '+1 (555) 401-3319'],
            ['Marcus Vance', ''],
            ['Sophia Patel', '+1 (555) 401-5508'],
            ['Walk-in', ''],
            ['Hannah Brooks', '+1 (555) 401-7712'],
            ['Tariq Hasan', '+1 (555) 401-8890'],
        ];

        $saleStmt = $pdo->prepare('INSERT INTO sales (invoice_no, user_id, customer_name, customer_phone, payment_method, discount_pct, subtotal, discount_amount, total_amount, rx_checked, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $itemStmt = $pdo->prepare('INSERT INTO sale_items (sale_id, medicine_id, medicine_name, qty, unit_price, line_total) VALUES (?, ?, ?, ?, ?, ?)');

        $invoiceCounter = 840;
        // Generate ~85 bills spread across the past 30 days (with plenty in last 7 days and today)
        for ($dayOffset = 29; $dayOffset >= 0; $dayOffset--) {
            $billsToday = $dayOffset === 0 ? 6 : ($dayOffset < 7 ? 5 : 2);
            for ($b = 0; $b < $billsToday; $b++) {
                $invoiceCounter++;
                $invoiceNo = sprintf('INV-%05d', $invoiceCounter);
                // Weight staff: Priya (3), Tomás (2), Elin (4)
                $staffId = $staffIds[($invoiceCounter + $b) % 3];
                if ($b % 4 === 0) {
                    $staffId = 3; // Priya slightly higher share
                }
                $cust = $customers[($invoiceCounter + $dayOffset) % count($customers)];
                $payment = $payments[($invoiceCounter + $b) % count($payments)];
                $discountPct = ($invoiceCounter % 5 === 0) ? 5 : (($invoiceCounter % 11 === 0) ? 10 : 0);

                $hour = 9 + (($b * 2 + $dayOffset) % 11); // 9am - 7pm
                $minute = ($invoiceCounter * 7) % 60;
                $timestamp = date('Y-m-d H:i:s', strtotime("-{$dayOffset} days") + ($hour * 3600) + ($minute * 60) - (int)date('H') * 3600 - (int)date('i') * 60);
                // Simpler deterministic timestamp on that day:
                $dateStr = date('Y-m-d', strtotime("-{$dayOffset} days"));
                $timestamp = sprintf('%s %02d:%02d:00', $dateStr, $hour, $minute);

                // Pick 2-4 items
                $numItems = 2 + ($invoiceCounter % 3);
                $subtotal = 0.0;
                $rxChecked = 0;
                $chosenItems = [];
                for ($i = 0; $i < $numItems; $i++) {
                    $med = $medRows[($invoiceCounter * 3 + $i * 5) % count($medRows)];
                    $qty = 1 + (($invoiceCounter + $i) % 5);
                    $lineTotal = round((float)$med['unit_price'] * $qty, 2);
                    $subtotal += $lineTotal;
                    if ((int)$med['rx_required'] === 1) {
                        $rxChecked = 1;
                    }
                    $chosenItems[] = [
                        'id' => $med['id'],
                        'name' => $med['brand_name'] . ' ' . $med['strength'],
                        'qty' => $qty,
                        'unit_price' => $med['unit_price'],
                        'line_total' => $lineTotal,
                    ];
                }

                $subtotal = round($subtotal, 2);
                $discountAmt = round($subtotal * ($discountPct / 100), 2);
                $total = round($subtotal - $discountAmt, 2);

                $saleStmt->execute([
                    $invoiceNo,
                    $staffId,
                    $cust[0],
                    $cust[1],
                    $payment,
                    $discountPct,
                    $subtotal,
                    $discountAmt,
                    $total,
                    $rxChecked,
                    $timestamp,
                ]);
                $saleId = (int)$pdo->lastInsertId();

                foreach ($chosenItems as $ci) {
                    $itemStmt->execute([
                        $saleId,
                        $ci['id'],
                        $ci['name'],
                        $ci['qty'],
                        $ci['unit_price'],
                        $ci['line_total'],
                    ]);
                }
            }
        }
    }
}
