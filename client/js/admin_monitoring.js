if (!localStorage.getItem("token") || localStorage.getItem("is_admin") != "1") {
    window.location.href = "login.html";
}

fetch("../../server/php/admin_monitoring.php", {
    headers: {
        "Authorization": "Bearer " + localStorage.getItem("token")
    }
})
.then((res) => res.json())
.then((stats) => {
    if (stats.error) {
        window.location.href = "login.html";
        return;
    }

    document.getElementById("monitor_stats").innerHTML = `
        <div class="stat_card"><span class="stat_num">${stats.requests_today}</span><span class="stat_label">Requests Today</span></div>
        <div class="stat_card"><span class="stat_num">${stats.error_rate_today}%</span><span class="stat_label">Error Rate</span></div>
        <div class="stat_card"><span class="stat_num">${stats.avg_response_ms}ms</span><span class="stat_label">Avg Response</span></div>
        <div class="stat_card"><span class="stat_num">${stats.p95_response_ms}ms</span><span class="stat_label">P95 Response</span></div>
    `;

    if (stats.alert_active) {
        document.getElementById("alert_banner").innerHTML = `
            <div class="empty_card" style="background-color: #B34A3C; color: #FFFFFF; margin-bottom: 24px;">
                <h3 style="color: #FFFFFF;">Alert</h3>
                <p style="color: #FFFFFF;">${stats.alert_message}</p>
            </div>
        `;
    }

    if (stats.recent_errors.length == 0) {
        document.getElementById("recent_errors_list").innerHTML = "<p style='color: #5A574F;'>No errors logged today.</p>";
    } else {
        let html = "";
        for (let i = 0; i < stats.recent_errors.length; i++) {
            const e = stats.recent_errors[i];
            html += `<div class="stat_row"><span>${e.endpoint} (${e.status_code})</span><strong>${e.response_time_ms}ms</strong></div>`;
        }
        document.getElementById("recent_errors_list").innerHTML = html;
    }
})
.catch(() => {
    document.getElementById("monitor_stats").innerHTML = "<p>something went wrong loading monitoring data</p>";
});