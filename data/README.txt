Data storage (temporary, before database)

- index.sections.json
  Stores editable .page-content sections for index.php.
  Admin writes to this JSON instead of rewriting index.php.

If you deploy, ensure the web server user can write to the /data folder and /Images/uploads for uploaded images.
