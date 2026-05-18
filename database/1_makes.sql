CREATE TABLE makes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    country TEXT NOT NULL,
    description TEXT
);

INSERT INTO makes (name, country, description) VALUES
    ('Ford', 'USA', 'Founded in 1903 by Henry Ford, known for pioneering mass production and iconic American muscle cars.'),
    ('Chevrolet', 'USA', 'Founded in 1911, Chevrolet has produced some of Americas most beloved sports and muscle cars.'),
    ('Dodge', 'USA', 'Known for powerful engines and bold styling, Dodge became a symbol of American muscle in the late 1960s.'),
    ('Ferrari', 'Italy', 'Founded in 1939 by Enzo Ferrari, Ferrari produces some of the worlds most desirable sports cars.'),
    ('Porsche', 'Germany', 'Founded in 1931, Porsche is renowned for precision engineering and timeless sports car design.'),
    ('Mercedes-Benz', 'Germany', 'One of the oldest car manufacturers in the world, founded in 1926, known for luxury and innovation.');
