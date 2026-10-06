# Export your data from MongoDB for the new Laravel panel

This copies everything from the old database (MongoDB Atlas) into the new one (MySQL).
Exporting is read-only: nothing is deleted or changed in MongoDB, and the current website keeps working.

You end up with **one `.json` file per collection** in a folder. That folder is what the import command reads.

## What to export

Export these 12 collections from your database. Some may be missing or empty: skip those.

| Collection | What it holds |
|---|---|
| `users` | Admin logins (passwords stay the same) |
| `posts` | Blog posts |
| `categories` | Blog categories |
| `case_studies` | Case studies |
| `partners` | Partner badges |
| `clients` | Client logos |
| `page_content` | Your edits to page text (merged into the full pages) |
| `seo` | SEO overrides per page |
| `leads` | Form enquiries |
| `subscribers` | Newsletter sign-ups |
| `media` | Uploaded images (stored inside MongoDB) |
| `settings` | Site settings, SMTP and the deploy hook |

**Which database?** It is the name in your Cloudflare variable `MONGODB_DB`. If you never set it, it is `gtech_red`.

**Before you export:** don't save anything in the old admin panel until the new panel is live, so nothing new is missed.
New form enquiries are fine: you can export `leads` again just before the switch.

---

## Option A: MongoDB Compass (recommended, no typing commands)

Compass is MongoDB's free desktop app.

1. **Install Compass.** Download it from https://www.mongodb.com/try/download/compass and install it.
2. **Get your connection string.**
   - In MongoDB Atlas, go to **Database** and click **Connect** on your cluster.
   - Choose **Compass** and copy the string. It looks like `mongodb+srv://gtech_app:<password>@cluster0.xxxxx.mongodb.net/`.
   - Replace `<password>` with the database user's password.
   - Tip: for this job, create a separate user under **Database Access** with the built-in role **Read any database**, and delete it afterwards.
3. **Connect.** Open Compass, paste the string into the **URI** box and click **Connect**.
4. **Open the database.** In the left sidebar, click your database (`gtech_red`, or your `MONGODB_DB` name). You see the collections listed above.
5. **Export each collection.** For each collection:
   1. Click the collection name.
   2. Click **Export Data** (top of the documents list), then **Export the full collection**.
   3. Choose **JSON** as the file type.
   4. Save it with exactly the collection's name, for example `posts.json` or `case_studies.json`, all in **one folder** such as `gtech-export`.
   5. Click **Export** and wait for "Export completed".
6. **Check the folder.** You should have up to 12 files, for example `users.json`, `posts.json`, `case_studies.json`, `media.json` and `settings.json`.
   Note the number of documents Compass shows for each collection. You will compare them after the import.

---

## Option B: `mongoexport` command line (faster if you are comfortable with a terminal)

1. **Install the tools.** Install **MongoDB Database Tools** from https://www.mongodb.com/try/download/database-tools.
2. **Copy your connection string** as in step 2 of Option A.
3. **Run the export.**

**Windows (PowerShell):**

```powershell
$uri = "mongodb+srv://gtech_app:YOUR_PASSWORD@cluster0.xxxxx.mongodb.net/gtech_red"
New-Item -ItemType Directory -Force gtech-export | Out-Null
foreach ($c in "users","posts","categories","case_studies","partners","clients","page_content","seo","leads","subscribers","media","settings") {
  mongoexport --uri="$uri" --collection=$c --jsonArray --out="gtech-export/$c.json"
}
```

**macOS / Linux:**

```bash
URI="mongodb+srv://gtech_app:YOUR_PASSWORD@cluster0.xxxxx.mongodb.net/gtech_red"
mkdir -p gtech-export
for c in users posts categories case_studies partners clients page_content seo leads subscribers media settings; do
  mongoexport --uri="$URI" --collection=$c --jsonArray --out="gtech-export/$c.json"
done
```

Each line prints `exported N records`. Note those numbers.
For a collection that doesn't exist, it prints `0 records`, which is fine.

> Alternative: `mongodump --uri="$URI" --out=dump` makes a full binary backup. Keep it as a safety copy if you like,
> but the importer needs the JSON files above.

---

## Import into the new panel (Laravel)

1. **Upload the folder.** Put the `gtech-export` folder on the Laravel server, for example in `storage/app/import`.
   On shared hosting, use the File Manager. On a VPS, use `scp`.
2. **Do a dry run.** From the Laravel folder, run this first. It reads every file and reports problems without saving anything:

   ```bash
   php artisan gtech:import-mongo storage/app/import --dry-run
   ```

3. **Run the real import.** Pass your old `AUTH_SECRET`, from the Cloudflare variables, so the SMTP password carries over:

   ```bash
   php artisan gtech:import-mongo storage/app/import --auth-secret="YOUR_OLD_AUTH_SECRET"
   php artisan gtech:seed-content
   ```

   - It prints a table: collection, how many were in the file, and how many were imported. The numbers should match what Compass or `mongoexport` reported.
   - Running it again is safe: it updates rows instead of creating duplicates. Re-export `leads` just before the switch and run the import again to pick up new enquiries.
4. **Check the panel.** Sign in at `https://YOUR-LARAVEL-SITE/admin` with your **existing email and password**.
   Old passwords work and are upgraded automatically on the first sign-in.
5. **Spot-check.** Open a few blog posts, case studies, leads and the Pages list. Check that images show: the old `/api/media/...` addresses keep working.
6. **Delete the export.** Delete the `gtech-export` folder from the server and your computer. It contains customer data from leads.

## If something goes wrong

| What you see | What to do |
|---|---|
| `unreadable` for a file | Re-export that collection as **JSON** (not CSV). |
| `media ... has no image data` | That image was saved without its file. Re-upload it in **Media library**. |
| `SMTP password not carried over` | Enter it again in **Site settings → Email** and click **Send test email**. |
| Counts don't match | Run the export again for that collection, then run the import again. |
