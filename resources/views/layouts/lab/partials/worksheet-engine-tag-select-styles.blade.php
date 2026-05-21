<style>
	.worksheet-engine-tag-select .tag-select-container {
		position: relative;
		cursor: text;
	}
	.worksheet-engine-tag-select .tag-select-input {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 6px;
		min-height: 42px;
		padding: 6px 12px;
		background: #fff;
		border: 2px solid #e0e0e0;
		border-radius: 8px;
		transition: all 0.3s ease;
	}
	.worksheet-engine-tag-select .tag-select-input:hover {
		border-color: #007bff;
	}
	.worksheet-engine-tag-select .tag-select-input:focus-within {
		border-color: #007bff;
		box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
		outline: none;
	}
	.worksheet-engine-tag-select .tag-badge {
		display: inline-flex;
		align-items: center;
		gap: 4px;
		padding: 4px 10px;
		background-color: #007bff;
		color: white;
		border-radius: 16px;
		font-size: 0.875rem;
		font-weight: 500;
		white-space: nowrap;
	}
	.worksheet-engine-tag-select .tag-badge i {
		cursor: pointer;
		font-size: 1rem;
		opacity: 0.8;
	}
	.worksheet-engine-tag-select .tag-badge i:hover {
		opacity: 1;
	}
	.worksheet-engine-tag-select .tag-input {
		flex: 1;
		min-width: 120px;
		border: none;
		outline: none;
		padding: 4px;
		font-size: 0.9rem;
	}
	.worksheet-engine-tag-select .tag-dropdown {
		position: absolute;
		top: 100%;
		left: 0;
		right: 0;
		background: white;
		border: 2px solid #007bff;
		border-top: none;
		border-radius: 0 0 8px 8px;
		max-height: 250px;
		overflow-y: auto;
		z-index: 1060;
		box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
		margin-top: -2px;
	}
	.worksheet-engine-tag-select .tag-dropdown-item {
		padding: 10px 16px;
		cursor: pointer;
		transition: background-color 0.2s;
		border-bottom: 1px solid #f0f0f0;
	}
	.worksheet-engine-tag-select .tag-dropdown-item:hover {
		background-color: #f8f9fa;
	}
	.worksheet-engine-tag-select .tag-dropdown-item:last-child {
		border-bottom: none;
	}
</style>
