// Fix printReceipt function to work without method and status columns
const fs = require('fs');

// Read the current file
const currentContent = fs.readFileSync('c:/xampp/htdocs/marketvendor/marketvendor/payment-history.php', 'utf8');

// Fix the printReceipt function - remove method and status references and fix cell indices
const oldFunction = `                    paymentData = {
                        paymentId: cells[0].textContent.trim(),
                        loanId: cells[1].textContent.trim(),
                        borrower: cells[2].textContent.trim(),
                        date: cells[3].textContent.trim(),
                        amount: cells[4].textContent.trim(),
                        principal: cells[4].textContent.trim(),
                        interest: cells[5].textContent.trim(),
                        lateFees: cells[6].textContent.trim(),
                        totalAmount: cells[7].textContent.trim(),
                        method: cells[8].textContent.trim(),
                        status: cells[9].textContent.trim(),
                        email: row.dataset.email || ''
                    };`;

const newFunction = `                    paymentData = {
                        paymentId: cells[0].textContent.trim(),
                        loanId: cells[1].textContent.trim(),
                        borrower: cells[2].textContent.trim(),
                        date: cells[3].textContent.trim(),
                        amount: cells[4].textContent.trim(),
                        principal: cells[4].textContent.trim(),
                        interest: cells[5].textContent.trim(),
                        lateFees: cells[6].textContent.trim(),
                        totalAmount: cells[7].textContent.trim(),
                        email: row.dataset.email || ''
                    };`;

// Replace the old function with the new one
const fixedContent = currentContent.replace(oldFunction, newFunction);

// Write the fixed content back
fs.writeFileSync('c:/xampp/htdocs/marketvendor/marketvendor/payment-history.php', fixedContent, 'utf8');
console.log('Fixed printReceipt function - removed method and status references, fixed cell indices');
