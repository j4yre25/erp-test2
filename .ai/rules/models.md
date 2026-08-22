---
paths:
  - 'app/Models/**'
---

# Models

## Separate workforce employees from login users
Use employees as the workforce/personnel record for guards and staff. Users represent system login access and roles; employees may optionally link to users when that person needs account access. Deployment, DTR, payroll, and compensation relationships should point to employees/deployments, while SOA approval actor fields point to users.
