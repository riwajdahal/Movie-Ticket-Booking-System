-- Add payment_method column to bookings table
ALTER TABLE bookings ADD COLUMN payment_method VARCHAR(20) DEFAULT NULL;

-- Update existing confirmed bookings to have payment_method
UPDATE bookings SET payment_method = 'eSewa' WHERE status = 'Confirmed';
UPDATE bookings SET payment_method = 'Counter' WHERE status = 'Pending'; 