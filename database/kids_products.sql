-- Create kids_products table if it doesn't exist
CREATE TABLE IF NOT EXISTS kids_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    short_description VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    age_range VARCHAR(50) NOT NULL,
    price DECIMAL(10,2),
    sale_price DECIMAL(10,2),
    image VARCHAR(255),
    category VARCHAR(50) NOT NULL,
    features TEXT,
    specifications TEXT,
    stock_status VARCHAR(20) DEFAULT 'in_stock',
    display_order INT NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample kids products if table is empty
INSERT INTO kids_products (title, slug, short_description, description, age_range, price, sale_price, image, category, features, specifications, stock_status, display_order, active)
SELECT 
    'Beginner Robotics Kit', 
    'beginner-robotics-kit', 
    'An introductory robotics kit perfect for young beginners to learn the basics of robotics and coding through fun, hands-on projects.',
    '<p>The Beginner Robotics Kit is designed specifically for young learners taking their first steps into the exciting world of robotics and coding. This comprehensive kit includes everything needed to build and program simple robots that move, light up, and respond to their environment.</p>
    <p>With easy-to-follow instructions and colorful, snap-together components, children can start building right away without any prior experience. The kit includes a child-friendly programming interface that uses visual blocks, making it easy for kids to create sequences of actions for their robots.</p>
    <p>Through play and experimentation, children will develop fundamental STEM skills, logical thinking, and problem-solving abilities while having fun creating their own robotic creations.</p>',
    '7-10 years',
    89.99,
    79.99,
    'assets/images/kids/products/beginner-robotics-kit.jpg',
    'Robotics',
    '- 30+ colorful building components\n- Child-friendly visual programming interface\n- USB connection cable\n- Rechargeable battery pack\n- 10 guided project cards\n- Comprehensive instruction manual',
    'Age Range: 7-10 years\nBattery: Rechargeable Li-ion (included)\nConnectivity: USB\nSoftware Compatibility: Windows, Mac, Chromebook\nProgramming Language: Visual block-based\nNumber of Projects: 10+\nAssembly Time: 15-30 minutes per project',
    'in_stock',
    1,
    1
WHERE NOT EXISTS (SELECT 1 FROM kids_products LIMIT 1);

INSERT INTO kids_products (title, slug, short_description, description, age_range, price, sale_price, image, category, features, specifications, stock_status, display_order, active)
SELECT 
    'Advanced Robotics Lab Kit', 
    'advanced-robotics-lab-kit', 
    'A comprehensive robotics kit for older children and teens to explore advanced concepts in robotics, programming, and engineering.',
    '<p>The Advanced Robotics Lab Kit takes young inventors to the next level with a sophisticated set of components and sensors that allow for the creation of complex, interactive robots. This kit is perfect for children who have some experience with basic robotics or coding and are ready for more challenging projects.</p>
    <p>The kit includes a programmable microcontroller, multiple sensors (light, sound, touch, distance), servo motors, LED displays, and structural components that can be assembled in countless configurations. Children can build walking robots, sorting machines, interactive games, and much more.</p>
    <p>The included software introduces text-based programming concepts while still offering a visual interface, creating a perfect bridge to more advanced coding languages. Through guided projects and open-ended challenges, children will develop critical thinking, engineering principles, and creative problem-solving skills.</p>',
    '11-16 years',
    149.99,
    129.99,
    'assets/images/kids/products/advanced-robotics-kit.jpg',
    'Robotics',
    '- Programmable microcontroller brain\n- 6 different types of sensors\n- 4 servo motors\n- 200+ building components\n- LED display module\n- Wireless connectivity module\n- Rechargeable battery pack\n- 15 guided project tutorials\n- Storage case with sorting trays',
    'Age Range: 11-16 years\nBattery: Rechargeable Li-ion (included)\nConnectivity: USB and Bluetooth\nSoftware Compatibility: Windows, Mac, iOS, Android\nProgramming Languages: Visual blocks and text-based\nNumber of Projects: 15+ guided, unlimited custom\nAssembly Time: 30-90 minutes per project',
    'in_stock',
    2,
    1
WHERE NOT EXISTS (SELECT 1 FROM kids_products LIMIT 1);

INSERT INTO kids_products (title, slug, short_description, description, age_range, price, sale_price, image, category, features, specifications, stock_status, display_order, active)
SELECT 
    'Coding Adventure Board Game', 
    'coding-adventure-board-game', 
    'A fun and educational board game that teaches coding concepts through exciting adventures and puzzles without using a computer.',
    '<p>The Coding Adventure Board Game introduces children to fundamental programming concepts through an exciting, screen-free board game experience. Players embark on a journey through a colorful game world, solving puzzles and completing challenges using coding logic.</p>
    <p>Players use instruction cards to program their character\'s movements, learning about sequences, loops, conditionals, and debugging as they navigate through the game board. The game can be played at multiple difficulty levels, making it appropriate for a wide age range and allowing the game to grow with your child.</p>
    <p>Perfect for family game night or classroom activities, this game makes learning coding principles fun and accessible for everyone, even without any prior coding knowledge or computer access.</p>',
    '6-12 years',
    34.99,
    29.99,
    'assets/images/kids/products/coding-board-game.jpg',
    'Coding Games',
    '- Colorful game board with multiple paths and challenges\n- 4 player character pieces\n- 100+ coding instruction cards\n- 50 challenge cards with varying difficulty levels\n- Reference guide with coding concepts\n- Achievement tracking sheet\n- Cooperative and competitive game modes',
    'Age Range: 6-12 years\nPlayers: 2-4\nPlay Time: 30-45 minutes\nLearning Concepts: Sequences, loops, conditionals, functions, debugging\nDifficulty Levels: 3 (beginner, intermediate, advanced)\nLanguage: English\nContents: Game board, cards, pieces, instruction manual',
    'in_stock',
    3,
    1
WHERE NOT EXISTS (SELECT 1 FROM kids_products LIMIT 1);

INSERT INTO kids_products (title, slug, short_description, description, age_range, price, sale_price, image, category, features, specifications, stock_status, display_order, active)
SELECT 
    'Digital Inventor\'s Kit', 
    'digital-inventors-kit', 
    'An electronics kit that introduces children to circuits, coding, and digital making through creative projects that combine technology with arts and crafts.',
    '<p>The Digital Inventor\'s Kit bridges the gap between technology and creativity, allowing children to build interactive projects that respond to their environment. This kit introduces basic electronics and coding concepts through hands-on projects that incorporate arts and crafts materials.</p>
    <p>The kit includes a simple microcontroller board, various sensors and output components (lights, sounds, motors), and conductive materials that can be combined with everyday craft supplies. Children can create light-up greeting cards, interactive stuffed toys, musical instruments, and much more.</p>
    <p>The included project guide walks children through the basics of circuits and simple programming, while encouraging them to experiment and create their own unique inventions. This kit is perfect for creative children who might not be initially drawn to traditional robotics or coding activities.</p>',
    '8-14 years',
    69.99,
    59.99,
    'assets/images/kids/products/digital-inventors-kit.jpg',
    'Electronics',
    '- Kid-friendly microcontroller board\n- LED lights in multiple colors\n- Sound module and speaker\n- Motion and light sensors\n- Conductive tape and thread\n- Alligator clips and jumper wires\n- Motor and servo\n- Battery pack\n- 12 project guide with templates\n- Storage case',
    'Age Range: 8-14 years\nBattery: 3 AA batteries (not included)\nConnectivity: USB\nSoftware Compatibility: Windows, Mac\nProgramming Interface: Visual blocks\nNumber of Projects: 12 guided, unlimited custom\nAdditional Materials Needed: Basic craft supplies (paper, cardboard, fabric)',
    'in_stock',
    4,
    1
WHERE NOT EXISTS (SELECT 1 FROM kids_products LIMIT 1);