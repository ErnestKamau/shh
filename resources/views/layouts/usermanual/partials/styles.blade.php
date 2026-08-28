{{-- Shared styles + Lottie for User Manual hub --}}
@once
<style>
	.um-page {
		--um-blue: #7eb6d9;
		--um-blue-soft: #b8d4e8;
		--um-blue-wash: #eaf4fb;
		--um-blue-deep: #3d7ea6;
		--um-ink: #1e293b;
		--um-muted: #64748b;
		--um-border: #d7e6f2;
		--um-radius: 14px;
		color: var(--um-ink);
	}

	.um-main {
		background: #fff;
		border: 1px solid var(--um-border);
		border-radius: var(--um-radius);
		padding: 1.35rem 1.5rem 2rem;
		box-shadow: 0 12px 32px rgba(30, 41, 59, 0.05);
		animation: um-fade-in 0.45s ease;
	}

	@keyframes um-fade-in {
		from { opacity: 0; transform: translateY(8px); }
		to { opacity: 1; transform: translateY(0); }
	}

	.um-hero {
		display: grid;
		grid-template-columns: minmax(0, 1fr) 140px;
		gap: 1rem;
		align-items: center;
		padding: 1rem 1.15rem;
		margin: -0.35rem -0.35rem 1.35rem;
		border-radius: 12px;
		background: linear-gradient(125deg, var(--um-blue-wash) 0%, #fff 55%, #d9ecf7 100%);
		border: 1px solid var(--um-border);
	}

	@media (max-width: 767.98px) {
		.um-hero { grid-template-columns: 1fr; }
		.um-hero__lottie { justify-self: center; }
	}

	.um-hero__kicker {
		font-size: 0.7rem;
		font-weight: 700;
		letter-spacing: 0.05em;
		text-transform: uppercase;
		color: var(--um-blue-deep);
		margin: 0 0 0.35rem;
	}

	.um-hero h1 {
		font-size: 1.55rem;
		font-weight: 750;
		margin: 0 0 0.4rem;
		line-height: 1.2;
	}

	.um-hero p {
		margin: 0;
		color: var(--um-muted);
		font-size: 0.92rem;
		max-width: 38rem;
	}

	.um-hero__lottie {
		width: 130px;
		height: 130px;
	}

	.um-content h2 {
		font-size: 1.2rem;
		font-weight: 700;
		margin: 1.6rem 0 0.65rem;
		color: var(--um-ink);
	}

	.um-content h3 {
		font-size: 1.02rem;
		font-weight: 650;
		margin: 1.25rem 0 0.5rem;
		color: var(--um-blue-deep);
	}

	.um-content p {
		font-size: 0.92rem;
		line-height: 1.6;
		color: #334155;
		margin: 0 0 0.85rem;
	}

	.um-content ol, .um-content ul {
		margin: 0 0 1rem;
		padding-left: 1.25rem;
		color: #334155;
		font-size: 0.9rem;
		line-height: 1.55;
	}

	.um-content li { margin-bottom: 0.35rem; }

	.um-tip {
		display: flex;
		gap: 0.75rem;
		align-items: flex-start;
		padding: 0.85rem 1rem;
		border-radius: 12px;
		background: var(--um-blue-wash);
		border: 1px solid var(--um-border);
		margin: 1rem 0 1.25rem;
	}

	.um-tip__icon {
		width: 2rem;
		height: 2rem;
		border-radius: 999px;
		background: #fff;
		color: var(--um-blue-deep);
		display: grid;
		place-items: center;
		flex-shrink: 0;
	}

	.um-tip p { margin: 0; font-size: 0.85rem; }

	.um-figure {
		margin: 1rem 0 1.5rem;
		border-radius: 12px;
		border: 1px solid var(--um-border);
		overflow: hidden;
		background: var(--um-blue-wash);
		animation: um-fade-in 0.55s ease;
	}

	.um-figure img {
		display: block;
		width: 100%;
		height: auto;
		max-height: 420px;
		object-fit: contain;
		background: #f8fafc;
	}

	.um-figure figcaption {
		padding: 0.65rem 0.9rem;
		font-size: 0.78rem;
		color: var(--um-muted);
		border-top: 1px solid var(--um-border);
		background: #fff;
	}

	.um-figure--placeholder {
		min-height: 160px;
		display: grid;
		place-items: center;
		padding: 1.5rem;
		text-align: center;
		color: var(--um-muted);
		font-size: 0.85rem;
	}

	.um-figure--placeholder i {
		font-size: 2rem;
		color: var(--um-blue);
		display: block;
		margin-bottom: 0.4rem;
	}

	.um-compare {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 0.85rem;
		margin: 1rem 0 1.25rem;
	}

	@media (max-width: 767.98px) {
		.um-compare { grid-template-columns: 1fr; }
	}

	.um-compare__card {
		border: 1px solid var(--um-border);
		border-radius: 12px;
		padding: 0.9rem 1rem;
		background: linear-gradient(180deg, #fff, var(--um-blue-wash));
	}

	.um-compare__card h4 {
		font-size: 0.88rem;
		font-weight: 700;
		margin: 0 0 0.45rem;
		color: var(--um-blue-deep);
	}

	.um-compare__card p, .um-compare__card ul {
		font-size: 0.82rem;
		margin: 0;
	}

	.um-hub-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
		gap: 1rem;
		margin-top: 1.25rem;
	}

	.um-hub-card {
		display: block;
		text-decoration: none;
		color: inherit;
		border: 1px solid var(--um-border);
		border-radius: var(--um-radius);
		padding: 1.1rem 1.15rem;
		background: linear-gradient(165deg, #fff 0%, var(--um-blue-wash) 100%);
		transition: transform 0.2s ease, box-shadow 0.2s ease;
		min-height: 150px;
	}

	.um-hub-card:hover {
		transform: translateY(-3px);
		box-shadow: 0 14px 28px rgba(61, 126, 166, 0.14);
		text-decoration: none;
		color: inherit;
	}

	.um-hub-card__icon {
		width: 2.5rem;
		height: 2.5rem;
		border-radius: 12px;
		background: linear-gradient(145deg, var(--um-blue-soft), var(--um-blue));
		color: #fff;
		display: grid;
		place-items: center;
		font-size: 1.25rem;
		margin-bottom: 0.75rem;
	}

	.um-hub-card h3 {
		font-size: 1rem;
		font-weight: 700;
		margin: 0 0 0.35rem;
	}

	.um-hub-card p {
		font-size: 0.8rem;
		color: var(--um-muted);
		margin: 0;
		line-height: 1.4;
	}

	.um-pager {
		display: flex;
		justify-content: space-between;
		gap: 0.75rem;
		margin-top: 2rem;
		padding-top: 1rem;
		border-top: 1px solid var(--um-border);
		flex-wrap: wrap;
	}

	.um-pager a {
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
		padding: 0.55rem 0.9rem;
		border-radius: 999px;
		background: var(--um-blue-wash);
		border: 1px solid var(--um-border);
		color: var(--um-blue-deep);
		font-size: 0.82rem;
		font-weight: 600;
		text-decoration: none;
	}

	.um-pager a:hover {
		background: var(--um-blue-soft);
		color: var(--um-ink);
		text-decoration: none;
	}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js" defer></script>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		if (typeof lottie === 'undefined') return;
		document.querySelectorAll('[data-um-lottie]').forEach(function (el) {
			var src = el.getAttribute('data-um-lottie');
			if (!src) return;
			try {
				lottie.loadAnimation({
					container: el,
					renderer: 'svg',
					loop: true,
					autoplay: true,
					path: src
				});
			} catch (e) {}
		});
	});
</script>
@endonce
