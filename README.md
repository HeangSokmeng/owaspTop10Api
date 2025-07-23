# OWASP API Security Top 10 – Vulnerabilities with Examples
A comprehensive guide covering the most critical API security vulnerabilities with practical examples and Laravel-specific solutions.
Table of Contents

1. Broken Object Level Authorization (BOLA)
2. Broken User Authentication
3. Excessive Data Exposure
4. Lack of Resources & Rate Limiting
5. Broken Function Level Authorization
6. Mass Assignment
7. Security Misconfiguration
8. Injection Attacks
9. Improper Asset Management
10. Insufficient Logging & Monitoring

## 1. Broken Object Level Authorization (BOLA)

![alt text](image-4.png)

-- Failures in this mechanism typically lead to unauthorized information disclosure, modification, or destruction of all data.
-- Example: A user can access and modify another user's data by manipulating the URL.

1. **Test**: Try accessing another user's resource by changing the ID.

2. **Example Attack**

   1. `GET /api/users/19/profile` ← your own
   2. `GET /api/users/20/profile` ← another user's

      **The issue:**
      When a user login with an account that has id = 19, they are able to access not only their own profile but also the profile of other users, such as user with id = 20 to get information.

   **Similarly:**

   1. `GET /api/users/19/orders` ← your own
   2. `GET /api/users/19/wishlist` – This should return only the logged-in user's wishlist (e.g., their favorite products or categories).
   3. `GET /api/users/20/wishlist` – This incorrectly allows the logged-in user (with id = 19) to access another user's wishlist.

3. **Expected Fix: The Solutions**

   - The backend should verify that the user_id in the request matches the auth_user_id (the ID of the authenticated user)
   - This can be fixed using Laravel Sanctum, Laravel Passport, or JWT by creating a middleware that:
     - Requires the user to be logged in (authenticated)
     - After Logged Checks that the user_id in the request matches the authenticated user's ID, allowing users to access only their own profile or related resources.
     - And have two ways can do 1. user without id requested and 2. user with id requested

   **Login**
   ![alt text](image.png)

   **non secure**
   ![alt text](image-1.png)

   **secure**
   ![alt text](image-2.png)

   **Or can use the same id in the route (e.g. /profile/{id}), but must check that the requested id matches the authenticated user's ID in the backend.**
   ![alt text](image-3.png)

---

## 2. Broken User Authentication

![alt text](image-5.png)

-- Attackers can gain complete control of other users' accounts in the system, read their personal data, and perform sensitive actions on their behalf. Systems are unlikely to be able to distinguish attackers' actions from legitimate user ones.

- **Example**:

  1. **Test**: Try using a weak token or a missing token.
     
     ```http
     POST /api/user/data
     Header: Authorization: Bearer abc123 ← test if this is accepted
     ```
     
     in this case missing on not set middleware in route
     
     ```php
     Route::post('/user/data', [UserController::class, 'data']);
     ```
     
     **solution**
     
     ```php
     Route::get('/secure', [UserController::class, 'getProfileSecure'])->middleware('isLoggin');
     ```

  2. **Test**: Try using a weak password
     
     ```http
     POST /api/auth/register
     Body: { "username": "admin", "password": "123456" }
     ```
     
     in this case password validation is not set

     example:
     ```php
     $validator = Validator::make($req->all(), ['password' => 'required|string',]);
     ```
     ![alt text](image-6.png)
     ![alt text](image-13.png)
     ![alt text](image-14.png)

     **solution**
     
     example validate form when user register:
     ```php
     $validator = Validator::make($req->all(), [
         'password' => [
             'required',
             'string',
             'min:8',
             'regex:/[a-z]/',
             'regex:/[A-Z]/',
             'regex:/[0-9]/',
             'regex:/[@$!%*?&#]/',
             'confirmed'
         ],
     ]);
     ```

     ![alt text](image-7.png)
     ![alt text](image-8.png)
     ![alt text](image-9.png)

  3. **Test**: Try using Secure the Login Route.
     - Throttle requests to avoid brute force
     - Validate credentials strictly
     - Use rate limiting (Laravel built-in):
     
     in the case not set rating limit on request endpoint login
     
     example:
     ```php
     Route::post('/login', [UserController::class, 'login']);
     ```
     ![alt text](image-10.png)

     **solution**
     
     example:
     ```php
     Route::middleware('throttle:daily-limit')->post('/login', [UserController::class, 'loginSecure']);
     ```
     ![alt text](image-11.png)

  4. **Test**: Doesn't generate a secret key but uses a custom secret key
     - To fix: Use a secure secret key by commands in laravel:
       ```bash
       composer require tymon/jwt-auth
       php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
       php artisan jwt:secret
       php artisan config:clear
       ```
     - Example Attacks
       ```bash
       command : hashcat -a 0 -m 16500 token.txt jwt.secrets.list
       wordList : https://github.com/wallarm/jwt-secrets/tree/master
       ```
       ![alt text](image-12.png)

  5. **Test**: Doesn't validate the JWT expiration date.
     - **Problems with Non-Expiring JWTs**
       - **Token Theft Risk**
         If someone steals the token (e.g. from local storage, logs, intercepted traffic), and the token never expires, they can access the system forever without re-authentication.
       - **No Revocation**
         - JWTs are stateless — the server doesn't store them. That means:
         - You can't invalidate a token after issuing it.
         - Even if a user logs out, the token still works unless it's manually blacklisted.
       - **Brute Force Vulnerability**
         - Access your system without login
     - **Solution**
       - in .env
         ```env
         JWT_TTL=120   //120 = 2h
         ```
       - in config/jwt.php
         ```php
         'ttl' => env('JWT_TTL', 60), or  'ttl' => (int) env('JWT_TTL', 60),
         ```
       - command:
         ```bash
         php artisan config:clear
         php artisan cache:clear
         php artisan config:cache
         ```

   - **Test**: Doesn't validate the JWT expiration date.
   - **Expected Fix**: Tokens should expire, be signed securely, and verified on each request.

---

## 3. Excessive Data Exposure

![alt text](image-20.png)

-- Exploitation of Excessive Data Exposure is simple, and is usually performed by sniffing the traffic to analyze the API responses, looking for sensitive data exposure that should not be returned to the user.
-- Excessive Data Exposure commonly leads to exposure of sensitive data.

![alt text](image-16.png)

1. **Test**: Check if sensitive data is returned.

2. **Example Response**:

   ![alt text](image-19.png)

   - Issue: we are working on product id, name, title, and created_at but why return creator?

3. To fix "Excessive Data Exposure" in Laravel—especially when using select or selectRaw thats want to only select the columns truly need and avoid returning entire models unnecessarily.
   - to

   ![alt text](image-18.png)

   ![alt text](image-17.png)

   **Expected Fix in this case**: Only return needed fields in responses.

---

## 4. Lack of Resources & Rate Limiting

![alt text](image-21.png)

--Exploitation requires simple API requests. No authentication is required. Multiple concurrent requests can be performed from a single local computer or by using cloud computing resources.
--Exploitation may lead to DoS, making the API unresponsive or even unavailable.
    ```
        No Rate Limiting on Login Endpoint:
            Allows brute force or credential stuffing attacks
        No Limit on Search Results:
            A user sends a query and retrieves 1 million records, crashing the system or exposing data.
        No Upload Size Restriction:
            A user uploads a 5GB file, overloading disk or memory.
        No Concurrent Session Control:
            One user opens 500 parallel sessions to over-consume bandwidth.
    ```

1. **Test**: Send many requests quickly.
2. **Example**:
   ```bash
   for i in {1..100}; do curl http://api.test.com/login; done
   ```
    - not set limit rating

        ![alt text](image-22.png)

    - write python script to attacks like ddos

        ![alt text](image-23.png)

        ```
            {
                "attack_summary": {
                    "target_url": "http://192.168.18.53:8000/api/view/product",
                    "attack_date": "2025-07-23T00:35:12.551121",
                    "total_attacks": 214,
                    "successful_attacks": 214,
                    "rate_limited_attacks": 0,
                    "server_errors": 0,
                    "attack_duration": 115.3333535194397
                },
                "security_assessment": {
                    "status": "[CRITICAL] CRITICALLY VULNERABLE",
                    "risk_level": "CRITICAL",
                    "rate_limit_effectiveness": 0.0,
                    "success_rate": 1.0,
                    "server_stability": 1.0
                },
                "attack_type_breakdown": {
                    "API_SCRAPING": {
                    "total_requests": 214,
                    "success_rate": 1.0,
                    "avg_response_time": 1635.4677699436652
                    }
                },
                "recommendations": [
                    "[CRITICAL] Implement aggressive rate limiting (Laravel throttle middleware)",
                    "[CONFIG] Set strict limits: 60 requests/minute per IP for API endpoints",
                    "[SECURITY] Implement progressive rate limiting with exponential backoff",
                    "[BLOCKING] Add IP blocking for repeated violations (fail2ban style)",
                    "[MONITORING] Deploy real-time monitoring and alerting for attack patterns",
                    "[WAF] Deploy Web Application Firewall (WAF) with API protection",
                    "[DETECTION] Implement behavioral analysis for attack detection",
                    "[TESTING] Regular security testing and penetration testing",
                    "[RESPONSE] Set up incident response procedures for API attacks",
                    "[AUTH] Consider API authentication and authorization improvements"
                ]
            } ```
            
3. **Expected Fix**: Apply rate limits per IP or user token
    - set limit rating

        ![alt text](image-25.png)
        ![alt text](image-26.png)

    - write python script to attacks like ddos

        ![alt text](image-27.png)

        ``` 
            "attack_summary": {
                "target_url": "http://192.168.18.53:8000/api/view/product",
                "attack_date": "2025-07-23T01:03:18.883586",
                "total_attacks": 336,
                "successful_attacks": 120,
                "rate_limited_attacks": 216,
                "server_errors": 0,
                "attack_duration": 138.8990457057953
            },
            "security_assessment": {
                "status": "[WARNING] MODERATELY PROTECTED",
                "risk_level": "MEDIUM",
                "rate_limit_effectiveness": 0.6428571428571429,
                "success_rate": 0.35714285714285715,
                "server_stability": 1.0
            },
            "attack_type_breakdown": {
                "API_SCRAPING": {
                "total_requests": 336,
                "success_rate": 0.35714285714285715,
                "avg_response_time": 1198.6441626435235
                }
            }, ```


---

## 5. Broken Function Level Authorization

![alt text](image-28.png)

-- Exploitation requires the attacker to send legitimate API calls to an API endpoint that they should not have access to as anonymous users or regular, non-privileged users. Exposed endpoints will be easily exploited.
-- Such flaws allow attackers to access unauthorized functionality. Administrative functions are key targets for this type of attack and may lead to data disclosure, data loss, or data corruption. Ultimately, it may lead to service disruption.

1. **Test**: Use a lower-permission token to access admin actions.

2. **Example**
   ```http
   DELETE /api/admin/delete-user/123 ← with normal user token
   ```
   ![alt text](image-29.png)

   ![alt text](image-30.png)

   ![alt text](image-31.png)

   **Issue:**
   - **No middleware to check role permissions**
     - Routes (e.g., DELETE /api/view/product/{id}) are exposed without checking if the user has permission (e.g., is an admin).
   - **Returning routes or data that shouldn't be accessible**
     - Sensitive routes are accessible to all authenticated users or even unauthenticated users.
   - **Missing role-based logic inside controllers**
     - Example: No checks inside ProductController@destroy to confirm if the user is allowed to delete.

   **Example**
   ```php
   Route::prefix('view')->group(function () {
       Route::prefix('product')->group(function () {
           Route::post('', [ProductController::class, 'store']);
           Route::get('', [ProductController::class, 'index']);
           Route::get('/{id}', [ProductController::class, 'show']);
           Route::put('/{id}', [ProductController::class, 'update']);
           Route::delete('/{id}', [ProductController::class, 'destroy']);
       });
   });
   ```

3. **Expected Fix**: Check user roles/permissions in the backend.

   **Fix**

   - **1. Use Middleware to Enforce Role Permissions**
     - Create a middleware to check for role
       ```bash
       php artisan make:middleware jwtAuth
       ```

       ```php
       Route::prefix('view')->group(function () {
           Route::prefix('product')->group(function () {
               Route::get('', [ProductController::class, 'index']);
               Route::get('/{id}', [ProductController::class, 'show']);

               Route::middleware('isLoggin')->post('', [ProductController::class, 'store']);
               Route::middleware('jwtAuth')->put('/{id}', [ProductController::class, 'update']);
               Route::middleware('jwtAuth')->delete('/{id}', [ProductController::class, 'destroy']);
           });
       });
       ```

   - **2. Restrict Sensitive Routes**
     - Remove or disable routes you don't want exposed
     - If in frontend display list of products and product details only

       ```php
       Route::prefix('view')->group(function () {
           Route::prefix('product')->group(function () {
               Route::get('', [ProductController::class, 'index']);
               Route::get('/{id}', [ProductController::class, 'show']);

               // Route::middleware('isLoggin')->post('', [ProductController::class, 'store']);
               // Route::middleware('jwtAuth')->put('/{id}', [ProductController::class, 'update']);
               // Route::middleware('jwtAuth')->delete('/{id}', [ProductController::class, 'destroy']);
           });
       });
       ```

   - **3. Add In-Controller Authorization Logic (optional fallback)**
     - In case someone bypasses middleware
     - Example in update or delete
       ```php
       $user = UserService::getAuthUser();
       $product = Product::where('user_id', $user->id)->find($id);
       ```

   ![alt text](image-32.png)

---

## 6. Mass Assignment

1. **Test**: Try sending extra parameters in the request.
2. **Example**:
   ```json
   {
      "username": "test",
      "isAdmin": true
   }
   ```
3. **Expected Fix**: Use whitelisting to control which fields can be updated.

---

## 7. Security Misconfiguration

1. **Test**: Check for
   - Stack traces in error
   - Missing security headers
   - Open CORS policy (`Access-Control-Allow-Origin: *`)
2. **Fix**: Sanitize errors, use CSP headers, and configure CORS strictly.

---

## 8. Injection (SQL, NoSQL, Command Injection)

1. **Test**: Try sending injection payloads.
2. **Example**
   ```json
   {
      "username": "admin' OR '1'='1",
      "password": "anything"
   }
   ```
3. **Expected Fix**: Use prepared statements and input validation

---

## 9. Improper Asset Management

1. **Test**: Discover unused or old versions of APIs.
2. ```
   /api/v1/users
   /api/v2/users ← legacy, still active?
   ```
3. **Fix**: Keep inventory and disable old or unmaintained endpoints.

---

## 10. Insufficient Logging & Monitoring

1. **Test**: Trigger errors or failed login attempts.
2. **Fix**: Ensure such events are logged and alerting is in place
