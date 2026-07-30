document.addEventListener("DOMContentLoaded", function () {
    let timer_el = document.getElementById("timer");
    let time_limit = parseInt(timer_el.dataset.timeLimit);

    let remaining = time_limit;
    let form = document.getElementById('quiz-form');

    function format_time(seconds) {
        let mins = Math.floor(seconds / 60);
        seconds = seconds % 60;
        return mins + ":" + String(seconds).padStart(2, "0");
    }

    function tick() {
        if (remaining <= 0) {
            clearInterval(interval_id);
            timer_el.textContent = "Time's up!";
            form.submit();
            return;
        }
        timer_el.textContent = format_time(remaining);
        if (remaining <= 30) {
            timer_el.classList.add("timer-warning");
        }
        remaining--;
    }

    tick();
    let interval_id = setInterval(tick, 1000);

    window.addEventListener("beforeunload", function (e) {
        if (remaining > 0) {
            e.preventDefault();
            e.returnValue = "";
        }
    });
});