# API_SERRA

This is the central REST API for managing the sawmill core logistics, customer profiles, and order processing workflows.

## Key Features Implemented

*   **User & Session Management:** Secure asynchronous authentication using Bearer Tokens.
*   **Modular User Profiles:** Supports synchronized registration and profile updates. Includes an asynchronous account deletion route (`DELETE /api/customers/users`) that safely preserves historical customer records if orders exist.
*   **Smart Multi-Stock Validation:** Custom form request validation that chains all shopping cart line validation errors in a single string, returning clear, user-friendly product references instead of raw technical database IDs.

## Database Architecture

The system database structure is defined in the entity-relationship model. You can inspect the full diagram directly in the repository layout:

[Database ER Diagram](./MER_diagram.png)

## Requirements & Installation

*   PHP 8.2+
*   Laravel 11
*   MySQL / MariaDB

1. Clone the repository and navigate to the project directory:
   ```
   git clone https://github.com/alangiralt-dot/API_SERRA.git
   cd API_SERRA
   git checkout develop
   ```

2. Run `composer install` to recover dependencies:
   ```
   composer install
   ```

3. Initialize your environment configuration file and generate the application key:
   ```
   cp .env.example .env
   php artisan key:generate
   ```

4. Open the `.env` file and modify the database connection parameters to match your local environment:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=serra_api
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Clear the configuration cache to force the auto-registration of package drivers:
   ```
   php artisan config:clear
   ```

6. Reset the database layout and populate it with all datasets and bot variables:
   ```
   php artisan migrate:fresh --seed
   php artisan db:seed --class=BotDataSeeder
   ```

7. Generate the OAuth2 encryption keys required for authentication tokens without prompts:
   ```
   php artisan passport:keys --force
   ```

8. Verify if port 8000 is free before launching the application:
   ```
   netstat -ano | grep 8000
   ```
   *(If the terminal returns no text, the port is free and ready to use).*

9. Launch the local development server in the background to expose the API endpoints:
   ```
   php artisan serve --port=8000 &
   ```
   *The backend REST API will be active and listening for JSON requests at `http://127.0.0.1:8000`*

10. Launch and keep the MySQL/MariaDB database server active:
    *   Open the **XAMPP Control Panel** application on your computer.
    *   Locate the **MySQL** service module in the list.
    *   Click the **Start** button associated with MySQL to execute the database engine in the background.
    *   Verify that the module background highlights in green and displays the default port **3306**.
    *(Note: The Apache module can remain turned off, as HTTP traffic is already managed independently by the Laravel development server on port 8000).*
    
## API Endpoints Reference

---

### RESOURCE 1: Customers (Profile & Authentication Management)

---

#### `Route::post('/customers', [CustomerController::class, 'store'])`

**URL:**
```
http://localhost:8000/api/customers
```

**Body:**
```
{
    "first_name": "Joan",
    "last_name": "Ferrer",
    "phone": "600445566",
    "street": "Carrer Verd",
    "address_number": "35",
    "address_floor": "3",
    "door": "2",
    "postal_code": "17083",
    "city_name": "Girona",
    "province_name": "Girona",
    "email": "joan.ferrer@test.com",
    "password": "ferrer17083"
}
```

**Description:** Allows the registration of a new customer by creating their account in the system.

**Response (201 Created):**
```
{
    "status": "success",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
        "token_type": "Bearer"
    }
}
```
---

#### `Route::post('/customers/tokens', [CustomerController::class, 'login'])`

**URL:**
```
http://localhost:8000/api/customers/tokens
```

**Body:**
```
{
    "email": "info@fusteriasaubi.com",
    "password": "saubi17190"
}
```

**Description:** Allows the customer to log in by validating their data to give them access to private areas.

**Response (201 Created):**
```
{
    "status": "success",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
        "token_type": "Bearer"
    }
}
```

---

#### `Route::delete('/customers/tokens', [CustomerController::class, 'logout'])`

**URL:**
```
http://localhost:8000/api/customers/tokens
```

**Header:** Bearer Token (Admin is not global)

**Description:** Delete all tokens of the customer in the oauth_access_tokens table.

**Response (200 OK):**
```
{
    "status": "success",
    "message": "Session successfully closed."
}
```

---

#### `Route::get('/customers/profiles', [CustomerController::class, 'getProfile'])`

**URL:**
```
http://localhost:8000/api/customers/profiles
```

**Header:** Bearer Token (Admin is not global)

**Description:** Retrieves the personal and contact details of the connected client to be able to fill in the boxes of a form, for example.

**Response (200 OK):**
```
{
    "customer_id": 3,
    "first_name": "Joan",
    "last_name": "Ferrer",
    "phone": "600445566",
    "street": "Carrer Verd",
    "address_number": "35",
    "address_floor": "3",
    "door": "2",
    "postal_code": "17083",
    "city": "Girona",
    "province": "Girona"
}
```

---

#### `Route::put('/customers/profiles', [CustomerController::class, 'updateProfile'])`

**URL:**
```
http://localhost:8000/api/customers/profiles
```

**Header:** Bearer Token (Admin is not global)

**Body:**
```
{
    "first_name": "Carles",
    "last_name": "Saubí",
    "phone": "972230608",
    "street": "Carrer Cardenal Vidal i Barraquer",
    "address_number": "18",
    "address_floor": "Planta Baixa",
    "door": null,
    "postal_code": "17190",
    "city_name": "Girona",
    "province_name": "Girona"
}
```

**Description:** Update and save the profile information of the logged customer.

**Response (200 OK):**
```
{
    "status": "success",
    "message": "Profile successfully updated."
}
```

---

#### `Route::patch('/customers/{id}/roles', [CustomerController::class, 'updateRole'])`

**URL:**
```
http://localhost:8000/api/customers/1/roles
```

**Header:** Bearer Token (Admin only)

**Body:**
```
{
    "is_admin": true
}
```

**Description:** Allows an authenticated administrator to update a user's administrative privileges, acting either to grant or revoke admin status based on the provided boolean value.

**Response (200 OK):**
```
{
    "status": "success",
    "message": "User role successfully updated."
}
```

---

#### `Route::delete('/customers/users', [CustomerController::class, 'destroy'])`

**URL:**
```
http://localhost:8000/api/customers/users
```

**Header:** Bearer Token (Customer only)

**Description:** A customer permanently deletes their user and maintains their profile if they placed orders.

**Response (200 OK):**
```
{
    "status": "success",
    "message": "Account and access successfully removed."
}
```

---

### RESOURCE 2: Catalogue (Products & Inventory Data)

---

#### `Route::get('/menu', [CatalogueController::class, 'getMenu'])`

**URL:**
```
http://localhost:8000/api/menu
```

**Description:** Returns a flat list of all available product categories including their hierarchical parent relationships.

**Response (200 OK):**
```
[
    {
        "id": 11,
        "category": "Bigues de fusta laminades a l'autoclau",
        "father_id": 8
    },
    {
        "id": 2,
        "category": "Fusta",
        "father_id": 1
    },
    ...
]
```

---

#### `Route::get('/categories/{id}/products', [CatalogueController::class, 'showChildProducts'])`

**URL:**
```
http://localhost:8000/api/categories/4/products
```

**Description:** Returns information about the parent products belonging to a specific category and their respective child product variations.

**Response (200 OK):**
```
{
    "category": {
        "id": 4,
        "category": "Motllures de fusta Pi Gallec",
        "father_id": 3
    },
    "products": {
        "RODÓ DE FUSTA MASSÍS PI GALLEC": [
            {
                "id": 1,
                "reference": "90154000",
                "width": 10,
                "height": -1,
                "length": 2500,
                "current_unit_price": 1.16,
                "pack": 100,
                "stock": 0,
                "is_discontinued": 0,
                "father_product_id": 1,
                "availability_id": 1,
                "unit_id": 1,
                "father_product": {
                    "id": 1,
                    "name": "RODÓ DE FUSTA MASSÍS PI GALLEC",
                    "description": "Llistó de fusta massissa rodó de Pi gallec d'ús general en fusteria, manualitats, artesania, etc.",
                    "details": null,
                    "image_path": "products/rodo-de-fusta-massis-pi-gallec-sain10.webp",
                    "is_discontinued": 0,
                    "category_id": 4
                },
                "availability": {
                    "id": 1,
                    "availability": "24/48h",
                    "delay_weight": 10
                },
                "unit": {
                    "id": 1,
                    "unit": "€ / tira"
                }
            },
            ...
        ],
        ...
    }
}
```

---

#### `Route::get('/units', [CatalogueController::class, 'getUnits'])`

**URL:**
```
http://localhost:8000/api/units
```

**Description:** Returns the contents of the database table units.

**Response (200 OK):**
```
[
    {
        "id": 5,
        "unit": "€ / m2"
    },
    {
        "id": 3,
        "unit": "€ / m3"
    },
    {
        "id": 2,
        "unit": "€ / metre"
    },
    {
        "id": 1,
        "unit": "€ / tira"
    },
    {
        "id": 4,
        "unit": "€ / unitat"
    }
]
```

---

#### `Route::get('/availabilities', [CatalogueController::class, 'getAvailabilities'])`

**URL:**
```
http://localhost:8000/api/availabilities
```

**Description:** Returns the contents of the database table availabilities.

**Response (200 OK):**
```
[
    {
        "id": 1,
        "availability": "24/48h",
        "delay_weight": 10
    },
    {
        "id": 2,
        "availability": "Consultar",
        "delay_weight": 30
    },
    {
        "id": 3,
        "availability": "3/5 dies",
        "delay_weight": 20
    }
]
```

---

#### `Route::delete('/products/fathers/{id}', [CatalogueController::class, 'discontinueFatherProduct'])`

**URL:**
```
http://localhost:8000/api/products/fathers/1
```

**Header:** Bearer Token (Admin only)

**Description:** Removes a specific father product and all his child products from the catalogue.

Rule_1: We don't want NOT discontinued children who have a discontinued father.

**Response (200 OK):**
```
{
    "status": "success",
    "message": "Father product and all its child variants successfully discontinued."
}
```

---

#### `Route::delete('/products/children/{id}', [CatalogueController::class, 'discontinueChildProduct'])`

**URL:**
```
http://localhost:8000/api/products/children/132
```

**Header:** Bearer Token (Admin only)

**Description:** Removes a specific child product from the catalogue.

Rule_1: We don't want NOT discontinued children who have a discontinued father.

**Response (200 OK):**
```
{
    "status": "success",
    "message": "Product successfully discontinued."
}
```

---

#### `Route::put('/products/fathers/{id}', [CatalogueController::class, 'updateFatherProduct'])`

**URL:**
```
http://localhost:8000/api/products/fathers/42
```

**Header:** Bearer Token (Admin only)

**Body:**
```
{
    "is_discontinued": 1,
    "name": "SÒCOL CANALITZADOR DE FUSTA"
}
```

**Description:** Update the cells you want in the row you specified with the id from the father_products table.

Rule_1: We don't want NOT discontinued children who have a discontinued father.

Rule_2: If they request is_discontinued=1, we update the parent and discontinue all children.

Rule_3: If they request is_discontinued=0, we update the parent and do not modify the is_discontinue values ​​of any children.

**Response (200 OK):**
```
{
    "status": "success",
    "message": "Parent product updated successfully."
}
```

---

#### `Route::put('/products/children/{id}', [CatalogueController::class, 'updateChildProduct'])`

**URL:**
```
http://localhost:8000/api/products/children/164
```

**Header:** Bearer Token (Admin only)

**Body:**
```
{
    "is_discontinued": 0,
    "reference": "90154881A"
}
```

**Description:** Update the cells you want in the row you specified with the id from the child_products table.

Rule_1: We don't want NOT discontinued children who have a discontinued father.

Rule_2: We do not modify the parent and update the child except when the parent is discontinued and the request wants the child to be NOT discontinued.

**Response (200 OK):**
```
{
    "status": "success",
    "message": "Child product updated successfully."
}
```

---

#### `Route::post('/products', [CatalogueController::class, 'store'])`

**URL:**
```
http://localhost:8000/api/products
```

**Header:** Bearer Token (Admin only)

Body_1:
```
{
    "name": "SÒCOL CANALITZADOR",
    "image_path": "products/socol-canalitzador.webp",
    "category_id": 4,
    "description": "Compta amb un o diversos canals buits al seu interior per distribuir els cables de manera ordenada.",
    "child_products": [
        {
            "reference": "90154881",
            "width": 15,
            "height": 70,
            "length": 2200,
            "cost_unit_price": 0.4500,
            "current_unit_price": 1.2500,
            "pack": 10,
            "stock": 150,
            "availability_id": 1,
            "unit_id": 1
        },
        {
            "reference": "90154882",
            "width": 15,
            "height": 100,
            "length": 2500,
            "cost_unit_price": 0.6500,
            "current_unit_price": 1.8500,
            "pack": 10,
            "stock": 80,
            "availability_id": 1,
            "unit_id": 1
        }
    ]
}
```

Body_2:
```
{
    "father_product_id": 42,
    "child_products": [
        {
            "reference": "90154883",
            "width": 15,
            "height": 120,
            "length": 2500,
            "cost_unit_price": 0.7500,
            "current_unit_price": 1.9400,
            "pack": 10,
            "stock": 150,
            "availability_id": 1,
            "unit_id": 1
        }
    ]
}
```

**Description:** Stores a new parent product and its children (body_1) or adds child products to an existing parent product (body_2).

Rule_1: We don't want NOT discontinued children who have a discontinued father.

Rule_2: Every item is always born discontinued to avoid invalid combinations of is_discontinued values.

**Response (201 Created):**
```
{
    "status": "success",
    "father_product_id": 42
}
```

---

### RESOURCE 3: Orders (Checkout, Logistics & Financial Lines)

---

#### `Route::post('/orders/previews', [OrderController::class, 'calculateCartPreview'])`

**URL:**
```
http://localhost:8000/api/orders/previews
```

**Body:**
```
{
    "items": [
        {
            "id": 36,
            "quantity": 5
        },
        {
            "id": 37,
            "quantity": 3
        }
    ]
}
```

**Description:** Calculate all the details of the current unconfirmed order.

**Response (200 OK):**
```
{
    "id": null,
    "code": "-",
    "status": "En curs",
    "date": "12/09/2026 12:02",
    "order_availability": "-",
    "taxable_basis": 25,
    "tax": 5.25,
    "total": 30.25,
    "order_lines": [
        {
            "id": 36,
            "name": "LLISTÓ DE FUSTA D'AVET",
            "reference": "91548010",
            "width": 20,
            "height": 20,
            "length": 3000,
            "pack": 1,
            "quantity": 5,
            "unit_price": 2.87,
            "unit": "€ / tira",
            "subtotal": 14.35
        },
        {
            "id": 37,
            "name": "LLISTÓ DE FUSTA D'AVET",
            "reference": "91548025",
            "width": 35,
            "height": 20,
            "length": 3000,
            "pack": 1,
            "quantity": 3,
            "unit_price": 3.55,
            "unit": "€ / tira",
            "subtotal": 10.65
        }
    ]
}
```

---

#### `Route::get('/orders/line-subtotal', [OrderController::class, 'calculateLineSubtotal'])`

**URL:**
```
http://localhost:8000/api/orders/line-subtotal?id=6&quantity=60
```

**Description:** Calculates the price of a single line item in the shopping cart when the customer changes the quantity on the screen.

**Response (200 OK):**
```
{
    "subtotal": 390.6
}
```

---

#### `Route::get('/orders', [OrderController::class,'showOrders'])`

**URL:**
```
http://localhost:8000/api/orders
```

**Header:** Bearer Token (Admin is global)

**Description:** Displays the store's purchase history. Customers only see their personal list, while administrators can see all general workshop purchases.

**Response (200 OK):**
```
[
    {
        "id": 3,
        "customer_id": 2,
        "code": "SERRA-2026-00003",
        "status": "Confirmada",
        "date": "03/07/2026 11:15",
        "order_availability": "Consultar",
        "total_amount": 589.1
    },
    {
        "id": 2,
        "customer_id": 1,
        "code": "SERRA-2026-00002",
        "status": "En preparació",
        "date": "28/06/2026 15:45",
        "order_availability": "3/5 dies",
        "total_amount": 483.12
    },
    {
        "id": 1,
        "customer_id": 1,
        "code": "SERRA-2026-00001",
        "status": "Lliurada",
        "date": "10/06/2026 10:30",
        "order_availability": "24/48h",
        "total_amount": 228.09
    }
]
```

---

#### `Route::get('/orders/{id}', [OrderController::class,'showOrderDetails'])`

**URL:**
```
http://localhost:8000/api/orders/3
```

**Header:** Bearer Token (Admin is global)

**Description:** Shows the complete breakdown of an confirmed order with all fixed prices and quantities as they were at the time the purchase was made.

**Response (200 OK):**
```
{
    "id": 3,
    "code": "SERRA-2026-00003",
    "status": "Confirmada",
    "date": "03/07/2026 11:15",
    "order_availability": "Consultar",
    "base_imposable": 486.86,
    "iva": 102.24,
    "total_amount": 589.1,
    "order_lines": [
        {
            "name": "BIGA DE FUSTA LAMINADA PI AUTOCLAU VERD CLASSE 3",
            "reference": "91687010",
            "width": 90,
            "height": 90,
            "length": 12000,
            "quantity": 2,
            "sale_unit_price": 1750,
            "unit": "€ / m3",
            "subtotal": 340.2
        },
        {
            "name": "TAULA DE FUSTA VELLA ROURE 25/30MM",
            "reference": "91690088",
            "width": 200,
            "height": 30,
            "length": 900,
            "quantity": 10,
            "sale_unit_price": 81.48,
            "unit": "€ / m2",
            "subtotal": 146.66
        }
    ]
}
```

---

#### `Route::post('/orders', [OrderController::class, 'confirmOrder'])`

**URL:**
```
http://localhost:8000/api/orders
```

**Header:** Bearer Token (Customer only)

**Body:**
```
{
    "order_lines": [
        { "id": 6, "quantity": 60 },
        { "id": 132, "quantity": 4 },
        { "id": 149, "quantity": 9 }    
    ]
}
```

**Description:** Processes the final purchase by saving the order permanently with the prices frozen at that moment, adding the amounts and applying the corresponding taxes.

**Response (200 OK):**
```
{
    'status'   => 'success',
    "order_id": 7
}
```

---

#### `Route::get('/statuses', [OrderController::class, 'getStatuses'])`

**URL:**
```
http://localhost:8000/api/statuses
```

**Description:** Returns the contents of the database table statuses.

**Response (200 OK):**
```
[
    {
        "id": 1,
        "status": "Confirmada"
    },
    {
        "id": 2,
        "status": "En preparació"
    },
    {
        "id": 3,
        "status": "Lliurada"
    }
]
```

---

#### `Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])`

**URL:**
```
http://localhost:8000/api/orders/2/status
```

**Header:** Bearer Token (Admin only)

**Body:**
```
{
    "status_id": 3
}
```

**Description:** Allows you to change the delivery status of a purchase (such as changing it from confirmed to in preparation).

**Response (200 OK):**
```
{
    "status": "success",
    "message": "Order status successfully updated."
}
```