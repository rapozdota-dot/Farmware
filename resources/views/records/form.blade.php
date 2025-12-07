<style>
    :root {
        --form-bg: #ffffff;       /* White */
        --form-text: #0f4d26;     /* Dark green */
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .form-label {
        margin-bottom: 0;
    }

    .form-label-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        color: var(--form-text);
        font-weight: 600;
    }

    .form-input {
        padding: 1rem;
        border-radius: var(--radius);
        border: 2px solid var(--border);
        background: var(--form-bg);
        width: 100%;
        transition: all 0.3s ease;
        color: var(--form-text);
    }

    .form-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        outline: none;
    }

    /* Validation error card */
    .error-card {
        margin-top: 2rem;
        background: hsl(0, 84%, 97%);
        border: 2px solid hsl(0, 84%, 60%);
        border-left: 4px solid hsl(0, 84%, 60%);
    }

    .error-title {
        margin: 0;
        color: hsl(0, 84%, 50%);
        font-weight: 700;
    }

    .error-list {
        margin: 0;
        padding-left: 1.5rem;
        color: hsl(0, 84%, 40%);
    }
</style>
