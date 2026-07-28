let currentDay = "DAY 1";

async function loadSchedule() {
    const response = await fetch("data/matches.json");
    const data = await response.json();
    render(data[currentDay]);
}

function render(fields) {
    const schedule = document.getElementById("schedule");
    schedule.innerHTML = "";
    Object.keys(fields).forEach(field => {
        schedule.innerHTML +=
        `
            <div class="field-title">${field}</div>
        `;
        fields[field].forEach(match => {
            schedule.innerHTML +=
            `
            <div class="match">
                <div class="time">
                    ${match.time}
                </div>
                <div class="category">
                    ${match.category}
                </div>
                <div class="teams">
                    ${match.home.toUpperCase()} vs ${match.away.toUpperCase()}
                </div>
                <div class="score">
                    ${match.score}
                </div>
            </div>
            `;
        });
    });
}

document.querySelectorAll(".tab").forEach(tab=>{
    tab.addEventListener("click", async ()=>{
        document
        .querySelectorAll(".tab")
        .forEach(t=>t.classList.remove("active"));
        tab.classList.add("active");
        currentDay = tab.dataset.day;
        loadSchedule();
    });
});
loadSchedule();
