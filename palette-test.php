<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SwaadSeva Color Palette Test</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #FAFAFA;
            /* Ivory White */
            color: #212121;
            /* Charcoal Black */
        }

        header {
            background-color: #FF6F00;
            /* Spicy Orange */
            padding: 20px;
            color: white;
            text-align: center;
        }

        .card {
            background-color: #A5D6A7;
            /* Mint Green */
            border-radius: 12px;
            margin: 30px auto;
            padding: 20px;
            width: 80%;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            color: #212121;
        }

        .button-primary {
            background-color: #FF6F00;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        .button-secondary {
            background-color: #4FC3F7;
            /* Sky Blue */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            margin-left: 10px;
            cursor: pointer;
            font-size: 16px;
        }

        footer {
            background-color: #FF6F00;
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: 40px;
        }
    </style>
</head>

<body>

    <header>
        <h1>Welcome to SwaadSeva</h1>
        <p>Delicious Home-Style Meals for Students & Professionals</p>
    </header>

    <div class="card">
        <h2>Today's Special</h2>
        <p>Rice, Chapati, Dal Fry, Mix Veg, and Salad</p>
        <button class="button-primary">Order Now</button>
        <button class="button-secondary">View Menu</button>
    </div>

    <div class="card">
        <h2>Why Choose Us?</h2>
        <ul>
            <li>💯 Hygienic and Home-Cooked Meals</li>
            <li>📦 Daily Tiffin Delivery</li>
            <li>👨‍🎓 Student-Friendly Plans</li>
        </ul>
    </div>

    <footer>
        &copy; <?php echo date("Y"); ?> SwaadSeva | All Rights Reserved
    </footer>

</body>

</html>