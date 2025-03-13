-- Make sure we're using the right database
USE tummy_pillow_db;

-- Insert default products
INSERT INTO products (category_id, name, description, image_url, price, quantity_desc, is_active) 
VALUES
    -- Hot Deals Category (id: 1)
    (1, 'Garlic Cream Cheese Buns - Box of 6', 'Delicious garlic cream cheese buns, perfect for breakfast or snack', 'gccb1.jpg', 420.00, 'box of 6', TRUE),
    (1, 'Garlic Cream Cheese Buns - Box of 4 Big Size', 'Larger size garlic cream cheese buns with extra filling', 'gccb1.jpg', 420.00, 'box of 4', TRUE),
    (1, 'Chocolate Revel Bars - Box of 16', 'Indulgent chocolate revel bars with a fudgy center', 'crb2.jpg', 440.00, 'box of 16', TRUE),

    -- Bread Category (id: 2)
    (2, 'Cinnamon Rolls - Box of 4', 'Soft and fluffy cinnamon rolls with cream cheese frosting', 'cr3.jpg', 420.00, 'box of 4', TRUE),
    (2, 'Sourdough Bread - Large Loaf', 'Traditional sourdough bread with a crispy crust', 'sourdough.jpg', 180.00, 'loaf', TRUE),
    (2, 'Wheat Bread - Sliced', 'Healthy whole wheat bread, perfect for sandwiches', 'wheat.jpg', 95.00, 'loaf', TRUE),

    -- Pastries Category (id: 3)
    (3, 'Empanadas - Box of 4', 'Savory empanadas with beef and vegetable filling', 'empa1.jpg', 260.00, 'box of 4', TRUE),
    (3, 'Empanadas - Box of 12', 'Family size pack of our savory empanadas', 'empa1.jpg', 780.00, 'box of 12', TRUE),
    (3, 'Croissants - Box of 6', 'Buttery and flaky authentic croissants', 'croissants.jpg', 360.00, 'box of 6', TRUE),

    -- Cakes Category (id: 4)
    (4, 'Chocolate Cake - 8 inch', 'Rich chocolate cake with chocolate ganache', 'choco_cake.jpg', 650.00, '8 inch round', TRUE),
    (4, 'Vanilla Cake with Buttercream - 8 inch', 'Light vanilla cake with smooth buttercream frosting', 'vanilla_cake.jpg', 600.00, '8 inch round', TRUE),
    (4, 'Red Velvet Cake - 8 inch', 'Classic red velvet cake with cream cheese frosting', 'red_velvet.jpg', 680.00, '8 inch round', TRUE);