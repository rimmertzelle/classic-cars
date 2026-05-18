CREATE TABLE cars (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    make_id INTEGER NOT NULL REFERENCES makes(id),
    model TEXT NOT NULL,
    year INTEGER NOT NULL,
    color TEXT,
    engine TEXT,
    description TEXT
);

CREATE TABLE car_categories (
    car_id INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    PRIMARY KEY (car_id, category_id),
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

INSERT INTO cars (make_id, model, year, color, engine, description) VALUES
    (1, 'Mustang', 1965, 'Candy Apple Red', 'V8 4.7L', 'The original pony car that started a revolution. The 1965 Mustang offered style and performance at an affordable price.'),
    (1, 'Thunderbird', 1957, 'Colonial White', 'V8 5.1L', 'The iconic two-seater personal luxury car that defined the 1950s dream of open-road freedom.'),
    (2, 'Corvette C2', 1963, 'Sebring Silver', 'V8 5.4L', 'The Sting Ray coupe, considered by many to be the most beautiful Corvette ever made.'),
    (2, 'Camaro SS', 1969, 'Hugger Orange', 'V8 6.5L', 'Built to battle the Mustang, the 1969 Camaro SS is one of the most sought-after muscle cars of all time.'),
    (3, 'Charger R/T', 1969, 'Plum Crazy Purple', 'V8 7.2L (440 Magnum)', 'Made famous by the Dukes of Hazzard, the 1969 Charger is a quintessential American muscle car.'),
    (3, 'Challenger T/A', 1970, 'Go Mango Orange', 'V8 6.3L', 'Dodge''s answer to the Mustang and Camaro, the 1970 Challenger arrived just as the muscle car era peaked.'),
    (4, '250 GTO', 1962, 'Rosso Corsa', 'V12 3.0L', 'Often called the most beautiful car ever made. The 250 GTO is also one of the most valuable cars in the world.'),
    (4, 'Testarossa', 1984, 'Rosso Corsa', 'Flat-12 4.9L', 'The Testarossa became a cultural icon of the 1980s, famously featured in Miami Vice.'),
    (5, '911 (901)', 1964, 'Silver', 'Flat-6 2.0L', 'The original 911, designed by Ferdinand Porsche''s grandson, established a template that endures to this day.'),
    (5, '356 Speedster', 1956, 'Polo White', 'Flat-4 1.6L', 'The lightweight and open Speedster was beloved by racing drivers and enthusiasts for its pure driving experience.'),
    (6, '300SL Gullwing', 1954, 'Silver', 'Inline-6 3.0L', 'The Gullwing doors made the 300SL instantly recognisable. It was the fastest production car of its era.'),
    (6, '280SL Pagoda', 1968, 'Midnight Blue', 'Inline-6 2.8L', 'The elegant Pagoda-roof roadster combined refined luxury with sporting character in classic 1960s style.');

-- Mustang: Pony Car, Muscle Car
INSERT INTO car_categories (car_id, category_id) VALUES (1, 5), (1, 1);
-- Thunderbird: Grand Tourer
INSERT INTO car_categories (car_id, category_id) VALUES (2, 4);
-- Corvette C2: Sports Car
INSERT INTO car_categories (car_id, category_id) VALUES (3, 2);
-- Camaro SS: Muscle Car, Pony Car
INSERT INTO car_categories (car_id, category_id) VALUES (4, 1), (4, 5);
-- Charger R/T: Muscle Car
INSERT INTO car_categories (car_id, category_id) VALUES (5, 1);
-- Challenger T/A: Muscle Car, Pony Car
INSERT INTO car_categories (car_id, category_id) VALUES (6, 1), (6, 5);
-- Ferrari 250 GTO: Sports Car, Grand Tourer
INSERT INTO car_categories (car_id, category_id) VALUES (7, 2), (7, 4);
-- Ferrari Testarossa: Sports Car
INSERT INTO car_categories (car_id, category_id) VALUES (8, 2);
-- Porsche 911: Sports Car
INSERT INTO car_categories (car_id, category_id) VALUES (9, 2);
-- Porsche 356 Speedster: Sports Car
INSERT INTO car_categories (car_id, category_id) VALUES (10, 2);
-- Mercedes 300SL Gullwing: Sports Car, Grand Tourer
INSERT INTO car_categories (car_id, category_id) VALUES (11, 2), (11, 4);
-- Mercedes 280SL Pagoda: Luxury, Grand Tourer
INSERT INTO car_categories (car_id, category_id) VALUES (12, 3), (12, 4);
