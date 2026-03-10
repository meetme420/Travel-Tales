# Setting up M-Pesa Callback with ngrok

To test M-Pesa's STK Push locally, Safaricom needs to reach your computer from the internet. Since `localhost` is private, we use **ngrok** to create a public tunnel.

## Step 1: Install ngrok
1.  Go to [ngrok.com](https://ngrok.com/) and create a free account.
2.  Download and install ngrok on your system.
3.  Connect your account by running:
    ```bash
    ngrok config add-authtoken YOUR_AUTH_TOKEN
    ```

## Step 2: Start the Tunnel
If your local server (XAMPP/WAMP) is running on port 80, run:
```bash
ngrok http 80
```
*(If you are using port 8000 or similar, change `80` to your port).*

## Step 3: Get your Public URL
Look for the **Forwarding** line in the terminal. It will look like this:
`https://a1b2-c3d4.ngrok-free.app`

## Step 4: Update Travel-Tales Config
Open `c:\Users\sprag\Documents\GitHub\Travel-Tales\config\mpesa.php` and update the `MPESA_CALLBACK_URL`:

```php
// If your ngrok URL is https://a1b2.ngrok-free.app
define('MPESA_CALLBACK_URL', 'https://a1b2.ngrok-free.app/Travel-Tales/api/mpesa_callback.php');
```
*(Make sure the path matches where your project is stored in your web server's root folder).*

---
**Note:** The ngrok URL changes every time you restart it (on the free version). You must update the config file each time you start a new session.
