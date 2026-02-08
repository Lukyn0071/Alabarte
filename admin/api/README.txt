API endpoints:

- POST /admin/api/index_content.php
  JSON body:
    { action: update|delete|add, page: "index", sectionId: "...", fields: {...}, position?: start|end }

- POST /admin/api/upload_image.php
  multipart/form-data:
    image: <file>
  Header:
    X-CSRF-Token: <token>

Note: Both endpoints require admin session.
