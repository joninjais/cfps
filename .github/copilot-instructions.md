# Project Context & Rules for GitHub Copilot

## 1. Tech Stack Overview
frontend: Yii2 Framework 
backend: Yii2 Framework
database: mariadb from 11.8.6-MariaDB
php: 8.3.30
nginx: 1.26.3

## 2. Coding Standards
- Follow existing Yii2 and PHP patterns in the relevant application before introducing new abstractions or dependencies.
- Use model validation and parameterized database queries; inspect related models, schema, and call sites before changing behavior.
- Make database changes through Yii migrations with safe rollback behavior where practical.
- Follow the principle of least privilege for database and application access.

## 3. Response Tone & Style
- Provide clear and concise explanations.
- When suggesting code changes, thoroughly review the existing code for security vulnerabilities and recommend secure improvements aligned with the current architecture and functionality.
- Prioritize security, maintainability, and performance in recommendations.
- Avoid introducing breaking changes without proper justification and communication.
- Ensure that any new dependencies or libraries are compatible with the existing tech stack and do not introduce security vulnerabilities.
- Regularly review and update dependencies to mitigate known security risks.

## 4. Security & Best Practices
- Enforce authentication and authorization on protected actions.
- Do not expose credentials, secrets, or sensitive user data in code, logs, or responses.
- Validate and sanitize all user inputs to prevent common security vulnerabilities such as SQL injection and XSS.
- Use prepared statements and parameterized queries for all database interactions.
- Regularly review and update access controls and permissions to ensure the principle of least privilege is maintained.
- Implement logging and monitoring to detect and respond to security incidents in a timely manner.
- Regularly review and update security policies and procedures to address emerging threats.
- Never store or log plaintext passwords.