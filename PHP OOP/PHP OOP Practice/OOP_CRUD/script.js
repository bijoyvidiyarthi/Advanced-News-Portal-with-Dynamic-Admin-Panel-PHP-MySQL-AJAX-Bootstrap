document.addEventListener('DOMContentLoaded', function () {
    const alertBox = document.getElementById("alert-box");

    if (alertBox) {
        setTimeout(() => {
            //smoothly hide the alertBox after loading
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";
            // After the fade-out animation, remove it from the layout
            setTimeout(() => {
                alertBox.remove();
            }, 500);
        }, 5000);
    }
});