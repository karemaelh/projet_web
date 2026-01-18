// Update chaos meter
function updateChaos(change) {
	const fill = document.querySelector('.chaos-fill');
	const level = document.querySelector('.chaos-level');
	let currentValue = parseInt(level.textContent);
	currentValue = Math.max(0, Math.min(100, currentValue + change));
	level.textContent = currentValue + '%';
	fill.style.width = currentValue + '%';
	
	// Update status text
	const statusText = document.querySelector('.chaos-meter p strong');
	if (currentValue < 30) {
		statusText.textContent = 'Calm & Peaceful';
		statusText.style.color = '#10b981';
	}
	else if (currentValue < 60) {
		statusText.textContent = 'Moderate Stress';
		statusText.style.color = '#fbbf24';
	}
	else if (currentValue < 80) {
		statusText.textContent = 'High Stress';
		statusText.style.color = '#f59e0b';
	}
	else {
		statusText.textContent = 'FULL CHAOS MODE';
		statusText.style.color = '#ef4444';
	}
}

function redirectTo(page) {
	window.location.href = page + '.php';
}