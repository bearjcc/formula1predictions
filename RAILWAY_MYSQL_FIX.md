# Railway MySQL 1064 "near 10" Fix

If your deploy crashes with:

```
SQLSTATE[42000] [1064] You have an error in your SQL syntax... near '10' at line 1
```

**Cause:** Laravel's DB_URL parser turns numeric URL paths into integers, triggering this MySQL error.

**Fix:** Use explicit database variables instead of DB_URL.

## Steps (Railway Dashboard)

1. Open your **App** service (not the MySQL service).
2. Go to **Variables**.
3. **Delete** `DB_URL` if it exists.
4. **Add** these variables (replace `MySQL` with your MySQL service name if different):

   | Variable     | Value                     |
   |-------------|---------------------------|
   | DB_CONNECTION | mysql                   |
   | DB_HOST     | ${{MySQL.MYSQLHOST}}     |
   | DB_PORT     | ${{MySQL.MYSQLPORT}}     |
   | DB_DATABASE | ${{MySQL.MYSQLDATABASE}} |
   | DB_USERNAME | ${{MySQL.MYSQLUSER}}     |
   | DB_PASSWORD | ${{MySQL.MYSQLPASSWORD}} |

5. Redeploy.

The app will use these vars directly and skip URL parsing.
