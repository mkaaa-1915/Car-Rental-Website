<style>
.modal-container {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
}

.modal {
    background-color: #f7f7f7;
    margin: auto;
    padding: 30px;
    border-radius: 10px;
    width: 90%;
    max-width: 450px;
    position: relative;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.close-btn {
    color: #818181;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    position: absolute;
    right: 15px;
    top: 15px;
}

.close-btn:hover {
    color: #060414;
}

.auth-form {
    width: 100%;
}

.form-header {
    text-align: center;
    margin-bottom: 25px;
}

.form-header h2 {
    font-size: 1.8rem;
    margin-bottom: 8px;
    color: var(--text-color);
}

.form-header p {
    color: var(--text-alter-color);
    font-size: 0.9rem;
}

.input-group {
    margin-bottom: 15px;
}

.input-group input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 0.95rem;
    outline: none;
    box-sizing: border-box;
}

.input-group input:focus {
    border-color: var(--main-color);
}

.auth-btn {
    width: 100%;
    padding: 12px;
    background: var(--main-color);
    color: var(--bg-color);
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    margin-top: 10px;
    transition: 0.3s ease;
}

.auth-btn:hover {
    background: var(--second-color);
}

.separator {
    text-align: center;
    margin: 20px 0;
    position: relative;
    color: var(--text-alter-color);
}

.separator::before,
.separator::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 40%;
    height: 1px;
    background: #ddd;
}

.separator::before {
    left: 0;
}

.separator::after {
    right: 0;
}

.social-login {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.social-btn {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.9rem;
    transition: 0.3s ease;
}

.social-btn:hover {
    background: #f7f7f7;
}

.switch-auth {
    text-align: center;
    margin-top: 20px;
    font-size: 0.9rem;
    color: var(--text-alter-color);
}

.switch-auth a {
    color: var(--main-color);
    text-decoration: none;
    font-weight: 500;
}

.switch-auth a:hover {
    text-decoration: underline;
}

.hidden {
    display: none;
}
</style>

