/**
 * Toggles password visibility and switches icon
 */


function toggleVisibility(fieldId) {
    const input = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');

    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = "password";
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

// OverlayScrollbars Configure
const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
const Default = {
    scrollbarTheme: 'os-theme-light',
    scrollbarAutoHide: 'leave',
    scrollbarClickScroll: true,
};
document.addEventListener('DOMContentLoaded', function () {
    const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
    if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined) {
        OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
                theme: Default.scrollbarTheme,
                autoHide: Default.scrollbarAutoHide,
                clickScroll: Default.scrollbarClickScroll,
            },
        });
    }
});


/* =========================
       Ck editor function 
    ========================= */

//CKEditor
// Initialize CKEditor 5 with Image Upload Support

class MyUploadAdapter {
    constructor(loader) {
        this.loader = loader;
    }

    upload() {
        return this.loader.file.then(file => {
            return new Promise((resolve, reject) => {
                const data = new FormData();
                data.append('upload', file);

                fetch('upload_image.php', {
                    method: 'POST',
                    body: data,

                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.error) {
                            reject(result.error.message);
                        } else {
                            resolve({
                                default: result.url
                            });
                        }
                    })
                    .catch(error => {
                        reject('Image upload failed');
                    });
            });
        });
    }

    abort() { }
}

// Register upload adapter
function MyCustomUploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
        return new MyUploadAdapter(loader);
    };
}


// Initialize CKEditor 5 with custom upload adapter
document.addEventListener("DOMContentLoaded", function () {
    ClassicEditor
        .create(document.querySelector('#editor'), {
            extraPlugins: [MyCustomUploadAdapterPlugin],
            toolbar: [
                'heading', '|',
                'bold', 'italic', '|',
                'link', 'imageUpload', 'insertTable', 'blockQuote', '|',
                'bulletedList', 'numberedList', '|',
                'undo', 'redo'
            ],
            image: {
                toolbar: [
                    'imageStyle:full',
                    'imageStyle:side',
                    '|',
                    'imageTextAlternative'
                ]
            }
        })
        .then(editor => {
            console.log('CKEditor upload adapter loaded successfully');
        })
        .catch(error => {
            console.error(error);
        });
});


/* =========================
       Chart 1: Weekly Viewers
       Data Source: 
    ========================= */

// Make cards sortable
new Sortable(document.querySelector('.connectedSortable'), {
    group: 'shared',
    handle: '.card-header',
});

const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
cardHeaders.forEach(cardHeader => {
    cardHeader.style.cursor = 'move';
});


// Fetch weekly data from JSON
fetch('assets/json/weekly_view.json')
    .then(res => res.json())
    .then(data => {
        const dbDates = data.dates;
        const dbViews = data.views;

        // Calculate weekly total views
        const weeklyTotalViews = dbViews.reduce((sum, val) => sum + val, 0);

        // Convert views to weekly percentage ratings
        const weeklyViewRatings = weeklyTotalViews > 0
            ? dbViews.map(v => (v / weeklyTotalViews) * 100)
            : [];

        // ApexCharts options
        const sales_chart_options = {
            series: [
                {
                    name: 'Weekly View Rating',
                    data: weeklyViewRatings,
                },
            ],
            chart: {
                height: 300,
                type: 'area',
                toolbar: { show: false },
            },
            legend: { show: false },
            colors: ['#0d6efd'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' },
            xaxis: {
                type: 'category',
                categories: dbDates, // last 7 days
            },
            yaxis: {
                max: 100,
                labels: {
                    formatter: val => val.toFixed(1) + "%",
                },
            },
            tooltip: {
                y: {
                    formatter: val => val.toFixed(2) + "%",
                },
            },
        };

        // Render the chart
        const sales_chart = new ApexCharts(
            document.querySelector('#revenue-chart'),
            sales_chart_options
        );
        sales_chart.render();
    })
    .catch(err => console.error("Weekly view JSON fetch error:", err));





/* =========================
     Chart 2: Category-wise Viewers
     Data Source: category_view.json
  ========================= */

fetch('assets/json/category_view.json')
    .then(response => response.json())
    .then(data => {
        const categoryLabels = data.map(item => item.category);
        const categoryData = data.map(item => item.views);

        const categoryPieChartOptions = {
            chart: { type: 'pie', height: 232.7 },
            series: categoryData,
            labels: categoryLabels,
            colors: [
                '#0d6efd', '#20c997', '#ffc107', '#d63384', '#6f42c1', '#adb5bd', '#fd7e14', '#198754'
            ],
            legend: { position: 'right', horizontalAlign: 'center' },
            stroke: { width: 2, colors: ['#fff'] },
            tooltip: { y: { formatter: val => val + " views" } }
        };

        const categoryPieChart = new ApexCharts(
            document.querySelector("#category-view-pie-chart"),
            categoryPieChartOptions
        );

        categoryPieChart.render();
    })
    .catch(err => console.error('Error loading JSON:', err));


document.addEventListener("DOMContentLoaded", function () {



    /* =========================
       Chart 3: Author Performance
       Data Source: author_view.json
    ========================= */

    fetch('assets/json/author_view.json')
        .then(response => response.json())
        .then(data => {

            if (!data.monthly || !data.yearly) {
                console.error("Invalid author_view.json structure");
                return;
            }

            /* =========================
               Prepare Monthly Ratings
            ========================= */
            const monthlyTotalViews = data.monthly.reduce(
                (sum, item) => sum + item.views, 0
            );

            const monthlyAuthors = data.monthly.map(item => item.author);
            const monthlyRatings = data.monthly.map(item =>
                monthlyTotalViews > 0
                    ? Number(((item.views / monthlyTotalViews) * 100).toFixed(2))
                    : 0
            );

            /* =========================
               Prepare Yearly Ratings
            ========================= */
            const yearlyTotalViews = data.yearly.reduce(
                (sum, item) => sum + item.views, 0
            );

            const yearlyAuthors = data.yearly.map(item => item.author);
            const yearlyRatings = data.yearly.map(item =>
                yearlyTotalViews > 0
                    ? Number(((item.views / yearlyTotalViews) * 100).toFixed(2))
                    : 0
            );

            /* =========================
               Chart Initialization
            ========================= */
            const chart = new ApexCharts(
                document.querySelector("#author-performance-chart"),
                {
                    series: [{
                        name: 'Monthly View Rating (%)',
                        data: monthlyRatings
                    }],
                    chart: {
                        type: 'bar',
                        height: 320,
                        toolbar: { show: false }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 5
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: val => val + "%"
                    },
                    colors: ['#0d6efd'],
                    xaxis: {
                        categories: monthlyAuthors,
                        max: 100,
                        labels: {
                            formatter: val => val + "%"
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: val => val + "%"
                        }
                    }
                }
            );

            chart.render();

            /* =========================
               Toggle Logic
            ========================= */
            const monthlyBtn = document.getElementById("authorMonthlyBtn");
            const yearlyBtn = document.getElementById("authorYearlyBtn");

            /* =========================
               Default Active State
            ========================= */
            monthlyBtn.classList.add("btn-primary");
            monthlyBtn.classList.remove("btn-outline-primary");

            yearlyBtn.classList.add("btn-outline-primary");
            yearlyBtn.classList.remove("btn-primary");

            /* =========================
               Monthly Button Click
            ========================= */
            monthlyBtn.addEventListener("click", function () {

                chart.updateOptions({
                    xaxis: { categories: monthlyAuthors },
                    series: [{
                        name: 'Monthly View Rating (%)',
                        data: monthlyRatings
                    }]
                });

                // UI state
                monthlyBtn.classList.add("btn-primary");
                monthlyBtn.classList.remove("btn-outline-primary");

                yearlyBtn.classList.add("btn-outline-primary");
                yearlyBtn.classList.remove("btn-primary");
            });

            /* =========================
               Yearly Button Click
            ========================= */
            yearlyBtn.addEventListener("click", function () {

                chart.updateOptions({
                    xaxis: { categories: yearlyAuthors },
                    series: [{
                        name: 'Yearly View Rating (%)',
                        data: yearlyRatings
                    }]
                });

                // UI state
                yearlyBtn.classList.add("btn-primary");
                yearlyBtn.classList.remove("btn-outline-primary");

                monthlyBtn.classList.add("btn-outline-primary");
                monthlyBtn.classList.remove("btn-primary");
            });


        })
        .catch(err => console.error("Author view JSON error:", err));
});

/* =========================
       Chart 4: Hourly Post Reach Analysis
       Data Source: hourly_view.json
    ========================= */


/* =========================
   Chart 4: Hourly Post Reach Analysis
========================= */
document.addEventListener("DOMContentLoaded", () => {
    const topBtn = document.getElementById("hourlyTopPostsBtn");
    const allBtn = document.getElementById("hourlyAllPostsBtn");
    const chartEl = document.querySelector("#hourly-reach-chart");
    const summaryList = document.getElementById("hourly-summary-list");
    const totalEl = document.getElementById("hourly-total");

    let chart, hourlyData;

    fetch("assets/json/hourly_view.json")
        .then(r => r.json())
        .then(data => {
            hourlyData = data;

            // Default: Top 5 Posts
            updateView(getTopPosts(data.posts, 5));

            topBtn.addEventListener("click", function (e) {
                e.preventDefault();
                setActive(topBtn, allBtn);
                updateView(getTopPosts(data.posts, 5));
            });

            allBtn.addEventListener("click", function (e) {
                e.preventDefault();
                setActive(allBtn, topBtn);
                updateView(data.posts);
            });
        })
        .catch(err => console.error("Hourly JSON load error:", err));

    function setActive(activeBtn, inactiveBtn) {
        activeBtn.classList.add("active");
        inactiveBtn.classList.remove("active");
    }

    function getTopPosts(posts, limit) {
        return [...posts]
            .sort((a, b) => b.total_views - a.total_views)
            .slice(0, limit);
    }

    function updateView(posts) {
        renderChart(posts);
        renderSidebar(posts);
    }

    function renderSidebar(posts) {
        if (!summaryList) return;
        summaryList.innerHTML = "";

        if (!posts || posts.length === 0) {
            summaryList.innerHTML = `<li class="list-group-item text-center text-muted small py-4">No data available</li>`;
            return;
        }

        let totalForView = 0;

        posts.forEach(p => {
            totalForView += p.total_views;
            const li = document.createElement("li");
            li.className = "list-group-item d-flex justify-content-between align-items-center bg-transparent py-2";

            const displayTitle = p.title.length > 20 ? p.title.slice(0, 20) + "…" : p.title;

            li.innerHTML = `
                <div class="text-truncate me-2" title="${p.title}" style="max-width:160px">
                  <span class="small fw-medium">${displayTitle}</span>
                </div>
                <span class="badge bg-primary-subtle text-primary rounded-pill">
                  ${p.total_views.toLocaleString()}
                </span>`;
            summaryList.appendChild(li);
        });


        if (totalEl) {
            totalEl.textContent = totalForView.toLocaleString();
        }
    }

    function renderChart(posts) {
        if (!chartEl) return;

        const series = posts.map(p => ({
            name: p.title,
            data: p.hourly_views
        }));

        const options = {
            chart: { height: 350, type: "line", toolbar: { show: false } },
            series: series,
            stroke: { curve: "smooth", width: 3 },
            xaxis: { categories: hourlyData.labels },
            yaxis: { min: 0, title: { text: "Views / Hour" } },
            tooltip: {
                shared: true,
                y: { formatter: v => v + " views" }
            },
            legend: { position: "bottom", fontSize: "12px" },
            colors: series.map((_, i) => palette(i))
        };

        if (chart) {

            chart.updateOptions(options, false, true);
        } else {
            chart = new ApexCharts(chartEl, options);
            chart.render();
        }
    }

    function palette(i) {
        const colors = ["#0d6efd", "#198754", "#fd7e14", "#6f42c1", "#dc3545", "#20c997", "#0dcaf0", "#ffc107"];
        return colors[i % colors.length];
    }
});



//time ago function
// dashboard.js

/**
 *  Time Ago Function
 */
function timeAgo(dateParam) {
    if (!dateParam) return null;
    const date = new Date(dateParam.replace(/-/g, "/"));
    const today = new Date();
    const seconds = Math.round((today - date) / 1000);

    if (isNaN(seconds)) return dateParam;
    if (seconds < 5) return 'Just Now';

    const intervals = {
        'y': 31536000, 'mo': 2592000, 'd': 86400, 'h': 3600, 'm': 60
    };

    for (let key in intervals) {
        const interval = Math.floor(seconds / intervals[key]);
        if (interval >= 1) return `${interval}${key} ago`;
    }
    return `${seconds}s ago`;
}

function updateAllTimes() {
    document.querySelectorAll('.js-timeago').forEach(el => {
        const timestamp = el.getAttribute('data-time');
        if (timestamp) el.innerText = timeAgo(timestamp);
    });
}

/**
 *  AJAX Action Handler (Status & Delete)
 */
document.addEventListener('click', function (e) {
    // --- update status ---
    const updateBtn = e.target.closest('.btn-update-status');
    if (updateBtn) {
        const id = updateBtn.getAttribute('data-id');
        const status = updateBtn.getAttribute('data-status');

        const formData = new URLSearchParams();
        formData.append('action', 'update_status');
        formData.append('id', id);
        formData.append('status', status);

        fetch('comments.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    location.reload();
                } else {
                    alert('Update failed!');
                }
            });
    }

    // --- delete logic ---
    const deleteBtn = e.target.closest('.btn-delete-comment');
    if (deleteBtn) {
        if (confirm('Are you sure you want to delete this comment?')) {
            const id = deleteBtn.getAttribute('data-id');

            const formData = new URLSearchParams();
            formData.append('action', 'delete_comment');
            formData.append('id', id);

            fetch('comments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        // table row delete
                        deleteBtn.closest('tr').remove();
                    } else {
                        alert('Delete failed!');
                    }
                });
        }
    }
});

/**
 *  Initialization
 */
document.addEventListener('DOMContentLoaded', function () {
    updateAllTimes();
    setInterval(updateAllTimes, 60000); // update every minute
});














