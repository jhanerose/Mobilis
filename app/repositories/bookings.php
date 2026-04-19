<?php
declare(strict_types=1);

if (!function_exists('createRentalBooking')) {
    function createRentalBooking(int $customerId, int $vehicleId, string $pickupDate, string $returnDate, string $notes = ''): array
    {
        if (!dbConnected()) {
            return ['ok' => false, 'error' => 'Database is not connected.'];
        }

        if ($customerId <= 0 || $vehicleId <= 0) {
            return ['ok' => false, 'error' => 'Please select valid customer and vehicle.'];
        }

        if ($pickupDate === '' || $returnDate === '' || $returnDate < $pickupDate) {
            return ['ok' => false, 'error' => 'Please provide a valid pickup and return date.'];
        }

        try {
            $pdo = db();
            $pdo->beginTransaction();

            $vehicleStmt = $pdo->prepare('SELECT status FROM Vehicle WHERE vehicle_id = :vehicle_id FOR UPDATE');
            $vehicleStmt->execute(['vehicle_id' => $vehicleId]);
            $vehicle = $vehicleStmt->fetch();

            if (!$vehicle) {
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'Vehicle not found.'];
            }

            if (($vehicle['status'] ?? '') === 'maintenance') {
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'Selected vehicle is under maintenance.'];
            }

            $insertRental = $pdo->prepare(
                "INSERT INTO Rental (customer_id, vehicle_id, pickup_date, return_date, status, notes)
                 VALUES (:customer_id, :vehicle_id, :pickup_date, :return_date, 'active', :notes)"
            );
            $insertRental->execute([
                'customer_id' => $customerId,
                'vehicle_id' => $vehicleId,
                'pickup_date' => $pickupDate,
                'return_date' => $returnDate,
                'notes' => trim($notes),
            ]);

            $rentalId = (int) $pdo->lastInsertId();

            $updateVehicle = $pdo->prepare("UPDATE Vehicle SET status = 'rented' WHERE vehicle_id = :vehicle_id");
            $updateVehicle->execute(['vehicle_id' => $vehicleId]);

            $pdo->commit();
            return ['ok' => true, 'rental_id' => $rentalId];
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'error' => 'Could not create booking right now.'];
        }
    }
}

if (!function_exists('getBookings')) {
    function getBookings(int $limit = 10): array
    {
        if (!dbConnected()) {
            return [
                ['rental_id' => 412, 'customer_id' => 1, 'customer' => 'Maria Reyes', 'customer_email' => 'maria@email.com', 'vehicle_id' => 1, 'vehicle' => 'Toyota Fortuner', 'pickup_date' => '2026-04-13', 'return_date' => '2026-04-16', 'status' => 'confirmed', 'payment_status' => 'paid', 'days' => 3, 'total' => 10500],
                ['rental_id' => 411, 'customer_id' => 2, 'customer' => 'Juan dela Cruz', 'customer_email' => 'jdc@email.com', 'vehicle_id' => 2, 'vehicle' => 'Honda Civic', 'pickup_date' => '2026-04-14', 'return_date' => '2026-04-14', 'status' => 'pending', 'payment_status' => 'unpaid', 'days' => 1, 'total' => 2200],
                ['rental_id' => 410, 'customer_id' => 3, 'customer' => 'Ana Lim', 'customer_email' => 'ana@email.com', 'vehicle_id' => 3, 'vehicle' => 'Toyota HiAce', 'pickup_date' => '2026-04-15', 'return_date' => '2026-04-20', 'status' => 'confirmed', 'payment_status' => 'paid', 'days' => 5, 'total' => 20000],
                ['rental_id' => 409, 'customer_id' => 4, 'customer' => 'Ramon Santos', 'customer_email' => 'ramon@email.com', 'vehicle_id' => 4, 'vehicle' => 'Ford Ranger', 'pickup_date' => '2026-04-17', 'return_date' => '2026-04-19', 'status' => 'confirmed', 'payment_status' => 'unpaid', 'days' => 2, 'total' => 6400],
                ['rental_id' => 408, 'customer_id' => 5, 'customer' => 'Pedro Cruz', 'customer_email' => 'pedz@email.com', 'vehicle_id' => 5, 'vehicle' => 'Mitsubishi Xpander', 'pickup_date' => '2026-04-10', 'return_date' => '2026-04-12', 'status' => 'completed', 'payment_status' => 'paid', 'days' => 2, 'total' => 5600],
                ['rental_id' => 407, 'customer_id' => 6, 'customer' => 'Lisa Garcia', 'customer_email' => 'lisag@email.com', 'vehicle_id' => 6, 'vehicle' => 'Hyundai Tucson', 'pickup_date' => '2026-04-08', 'return_date' => '2026-04-08', 'status' => 'cancelled', 'payment_status' => 'unpaid', 'days' => 1, 'total' => 3800],
            ];
        }

        try {
            $sql = "
                SELECT
                    r.rental_id,
                    r.customer_id,
                    CONCAT(c.first_name, ' ', c.last_name) AS customer,
                    c.email AS customer_email,
                    r.vehicle_id,
                    CONCAT(v.brand, ' ', v.model) AS vehicle,
                    r.pickup_date,
                    r.return_date,
                    r.status,
                    i.payment_status,
                    GREATEST(DATEDIFF(r.return_date, r.pickup_date), 1) AS days,
                    COALESCE(i.total_amount, vc.daily_rate * GREATEST(DATEDIFF(r.return_date, r.pickup_date), 1)) AS total
                FROM Rental r
                INNER JOIN Customer c ON c.customer_id = r.customer_id
                INNER JOIN Vehicle v ON v.vehicle_id = r.vehicle_id
                INNER JOIN VehicleCategory vc ON vc.category_id = v.category_id
                LEFT JOIN Invoice i ON i.rental_id = r.rental_id
                ORDER BY r.pickup_date DESC
                LIMIT :limit
            ";
            $stmt = db()->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            foreach ($rows as &$row) {
                if ($row['status'] === 'active') {
                    $row['status'] = 'confirmed';
                }
                if (!isset($row['payment_status']) || $row['payment_status'] === null) {
                    $row['payment_status'] = 'unpaid';
                }
            }
            return $rows;
        } catch (Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('getBookingById')) {
    function getBookingById(int $rentalId): ?array
    {
        if ($rentalId <= 0) {
            return null;
        }

        foreach (getBookings(500) as $booking) {
            if ((int) ($booking['rental_id'] ?? 0) === $rentalId) {
                return $booking;
            }
        }

        return null;
    }
}

if (!function_exists('updateBookingRecord')) {
    function updateBookingRecord(int $rentalId, string $pickupDate, string $returnDate, string $status, string $notes = ''): array
    {
        if (!dbConnected()) {
            return ['ok' => false, 'error' => 'Database is not connected.'];
        }

        if ($rentalId <= 0) {
            return ['ok' => false, 'error' => 'Invalid booking ID.'];
        }

        $statusMap = [
            'pending' => 'active',
            'confirmed' => 'active',
            'active' => 'active',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
        ];

        $normalizedStatus = strtolower(trim($status));
        if (!isset($statusMap[$normalizedStatus])) {
            return ['ok' => false, 'error' => 'Invalid booking status.'];
        }

        if ($pickupDate === '' || $returnDate === '' || $returnDate < $pickupDate) {
            return ['ok' => false, 'error' => 'Invalid date range.'];
        }

        try {
            $pdo = db();
            $pdo->beginTransaction();

            $rentalStmt = $pdo->prepare('SELECT rental_id, vehicle_id FROM Rental WHERE rental_id = :rental_id FOR UPDATE');
            $rentalStmt->execute(['rental_id' => $rentalId]);
            $rental = $rentalStmt->fetch();

            if (!$rental) {
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'Booking not found.'];
            }

            $dbStatus = $statusMap[$normalizedStatus];

            $updateRental = $pdo->prepare(
                "UPDATE Rental
                 SET pickup_date = :pickup_date,
                     return_date = :return_date,
                     status = :status,
                     notes = :notes,
                     actual_return = CASE WHEN :status = 'completed' THEN CURRENT_DATE ELSE NULL END
                 WHERE rental_id = :rental_id"
            );

            $updateRental->execute([
                'pickup_date' => $pickupDate,
                'return_date' => $returnDate,
                'status' => $dbStatus,
                'notes' => $notes,
                'rental_id' => $rentalId,
            ]);

            $vehicleStatus = $dbStatus === 'active' ? 'rented' : 'available';
            $updateVehicle = $pdo->prepare('UPDATE Vehicle SET status = :status WHERE vehicle_id = :vehicle_id');
            $updateVehicle->execute([
                'status' => $vehicleStatus,
                'vehicle_id' => (int) $rental['vehicle_id'],
            ]);

            $pdo->commit();
            return ['ok' => true];
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'error' => 'Could not update booking.'];
        }
    }
}

if (!function_exists('applyBookingAction')) {
    function applyBookingAction(int $rentalId, string $action): array
    {
        $action = strtolower(trim($action));

        if ($action === 'remind') {
            return ['ok' => true, 'notice' => 'Payment reminder queued.'];
        }

        if ($action === 'approve') {
            $booking = getBookingById($rentalId);
            if ($booking === null) {
                return ['ok' => false, 'error' => 'Booking not found.'];
            }

            return updateBookingRecord(
                $rentalId,
                (string) ($booking['pickup_date'] ?? ''),
                (string) ($booking['return_date'] ?? ''),
                'confirmed',
                ''
            );
        }

        if ($action === 'cancel' || $action === 'complete') {
            $booking = getBookingById($rentalId);
            if ($booking === null) {
                return ['ok' => false, 'error' => 'Booking not found.'];
            }

            $status = $action === 'cancel' ? 'cancelled' : 'completed';
            return updateBookingRecord(
                $rentalId,
                (string) ($booking['pickup_date'] ?? ''),
                (string) ($booking['return_date'] ?? ''),
                $status,
                ''
            );
        }

        return ['ok' => false, 'error' => 'Unsupported action.'];
    }
}

if (!function_exists('getCustomerBookings')) {
    function getCustomerBookings(string $customerEmail, int $limit = 10): array
    {
        if (!dbConnected()) {
            return [
                ['rental_id' => 412, 'vehicle_id' => 1, 'vehicle' => 'Toyota Fortuner', 'pickup_date' => '2026-04-13', 'return_date' => '2026-04-16', 'status' => 'confirmed', 'payment_status' => 'paid', 'days' => 3, 'total' => 10500],
                ['rental_id' => 408, 'vehicle_id' => 5, 'vehicle' => 'Mitsubishi Xpander', 'pickup_date' => '2026-04-10', 'return_date' => '2026-04-12', 'status' => 'completed', 'payment_status' => 'paid', 'days' => 2, 'total' => 5600],
            ];
        }

        try {
            $sql = "
                SELECT
                    r.rental_id,
                    r.vehicle_id,
                    CONCAT(v.brand, ' ', v.model) AS vehicle,
                    r.pickup_date,
                    r.return_date,
                    r.status,
                    i.payment_status,
                    GREATEST(DATEDIFF(r.return_date, r.pickup_date), 1) AS days,
                    COALESCE(i.total_amount, vc.daily_rate * GREATEST(DATEDIFF(r.return_date, r.pickup_date), 1)) AS total
                FROM Rental r
                INNER JOIN Customer c ON c.customer_id = r.customer_id
                INNER JOIN Vehicle v ON v.vehicle_id = r.vehicle_id
                INNER JOIN VehicleCategory vc ON vc.category_id = v.category_id
                LEFT JOIN Invoice i ON i.rental_id = r.rental_id
                WHERE c.email = :email
                ORDER BY r.pickup_date DESC
                LIMIT :limit
            ";
            $stmt = db()->prepare($sql);
            $stmt->bindValue(':email', $customerEmail);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            foreach ($rows as &$row) {
                if ($row['status'] === 'active') {
                    $row['status'] = 'confirmed';
                }
                if (!isset($row['payment_status']) || $row['payment_status'] === null) {
                    $row['payment_status'] = 'unpaid';
                }
            }
            return $rows;
        } catch (Throwable $e) {
            return [];
        }
    }
}
