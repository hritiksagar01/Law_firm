import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

// Global quick stopwatch state for active billing in header
window.lawyerTimer = () => ({
    running: false,
    seconds: 8040, // 02:14:00 default demo
    matter: 'Marsh v. Castellan',
    matterCode: 'HO-2026-0042',
    timerInterval: null,
    
    get formattedTime() {
        const hrs = String(Math.floor(this.seconds / 3600)).padStart(2, '0');
        const mins = String(Math.floor((this.seconds % 3600) / 60)).padStart(2, '0');
        const secs = String(this.seconds % 60).padStart(2, '0');
        return `${hrs}:${mins}:${secs}`;
    },
    
    toggle() {
        this.running = !this.running;
        if (this.running) {
            this.timerInterval = setInterval(() => {
                this.seconds++;
            }, 1000);
        } else {
            clearInterval(this.timerInterval);
        }
    },
    
    reset() {
        this.running = false;
        clearInterval(this.timerInterval);
        this.seconds = 0;
    }
});

Alpine.start();
