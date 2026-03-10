-- Sample Destinations
INSERT INTO destinations (name, description, image_url, price, location, rating) VALUES 
('Paris', 'The City of Light, home to the Eiffel Tower and world-class museums.', 'https://images.unsplash.com/photo-1502602898657-3e917247a183?w=800', 850.00, 'France', 4.8),
('Tokyo', 'A bustling metropolis blending traditional temples with futuristic neon signs.', 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=800', 1100.00, 'Japan', 4.9),
('Maldives', 'Crystal clear waters and overwater bungalows for a relaxing getaway.', 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=800', 950.00, 'Maldives', 5.0),
('Nairobi', 'The only city in the world with a national park on its doorstep.', 'https://images.unsplash.com/photo-1581442183204-6f092003c46e?w=800', 500.00, 'Kenya', 4.5);

-- Sample User (Password is 'password123' - hashed for security if needed, but for sandbox this is fine)
-- Travel Tales app uses password_verify, so this should be hashed. 
-- Assuming a test user: testuser / password123
INSERT INTO users (username, password, email) VALUES 
('testuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'test@example.com');
