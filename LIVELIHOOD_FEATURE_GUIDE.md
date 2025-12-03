# Livelihood & Skills Registry Feature Guide

## Overview

The Livelihood & Skills Registry is a comprehensive module designed to document resident skills and expertise, track livelihood programs and training initiatives, facilitate skills-based job matching, and support economic development tracking for the barangay.

## Features

### 1. **Resident Skills Management**
- Document resident skills and expertise
- Track proficiency levels (Beginner, Intermediate, Advanced, Expert)
- Record years of experience for each skill
- Store certification information
- Add notes about skill capabilities

**Access:** Livelihood & Skills → Resident Skills tab

**Key Capabilities:**
- Add multiple skills to residents
- Update skill proficiency levels
- Track certifications (TESDA, etc.)
- View all resident skills in a comprehensive table
- Filter by skill category

### 2. **Livelihood Programs**
- Create and manage livelihood programs
- Track program types: Training, Livelihood, Microfinance, Skills Development
- Monitor program status (Planning, Ongoing, Completed, Cancelled)
- Set target beneficiaries and budget
- Assign program coordinators

**Access:** Livelihood & Skills → Livelihood Programs tab

**Program Information:**
- Program name and description
- Start and end dates
- Location
- Target number of beneficiaries
- Budget allocation
- Program coordinator assignment
- Real-time session and participant counts

### 3. **Training Sessions Management**
- Create training sessions within programs
- Link sessions to specific skills
- Assign trainers
- Set session dates, times, and duration
- Track maximum participants
- Monitor session status

**Access:** Training Sessions menu

**Session Details:**
- Session name and program association
- Related skill (optional)
- Trainer information
- Date, time, and duration
- Location
- Maximum capacity
- Current enrollment count

**Participant Management:**
- Enroll residents in training sessions
- Track attendance (Present, Absent, Excused)
- Monitor completion status (Not Started, In Progress, Completed, Dropped)
- Issue certificates upon completion
- Add remarks for each participant

### 4. **Job Opportunities & Matching**
- Post job opportunities
- Specify required skills
- Track applications from residents
- Match residents with suitable job opportunities based on skills

**Access:** Job Opportunities menu

**Job Posting Details:**
- Job title and description
- Required skills
- Employer information
- Employment type (Full-time, Part-time, Contract, Temporary, Freelance)
- Location and salary range
- Application deadline
- Opportunity status (Open, Closed, On Hold)

**Application Management:**
- Track resident applications
- Update application status (Applied, Under Review, Shortlisted, Rejected, Hired, Withdrawn)
- Add review notes
- Record reviewer and review date

### 5. **Comprehensive Reports**
- Skills summary by category
- Program participation statistics
- Training completion rates
- Job opportunity metrics
- Resident skills profiles

**Access:** Livelihood Reports menu

**Available Reports:**
- **Skills Summary:** Distribution of skills across residents by proficiency level
- **Program Participation:** Program statistics including sessions, participants, and completion rates
- **Training Completion:** Session-by-session training metrics
- **Job Opportunities:** Job posting and application statistics
- **Resident Skills Profile:** Individual resident skill inventory and training history

## Database Schema

### Core Tables

#### `skills`
- Stores available skills in the system
- Categories: Construction, Hospitality, Tailoring, Beauty, IT, Farming, Mechanics, Creative, Finance
- 18 pre-loaded sample skills

#### `resident_skills`
- Links residents to their skills
- Tracks proficiency level and years of experience
- Stores certification information
- Unique constraint prevents duplicate skill entries per resident

#### `livelihood_programs`
- Main program records
- Tracks program type, status, dates, and budget
- Links to program coordinator (user)

#### `training_sessions`
- Individual training sessions within programs
- Links to skills and programs
- Tracks trainer, date, time, and capacity

#### `training_participants`
- Enrollment records for training sessions
- Tracks attendance and completion status
- Records certificate issuance

#### `job_opportunities`
- Job postings with skill requirements
- Tracks employer information and employment type
- Manages opportunity status and deadlines

#### `job_applications`
- Resident applications for job opportunities
- Tracks application status and review information
- Links residents to opportunities

## Workflow Examples

### Example 1: Documenting Resident Skills

1. Go to **Livelihood & Skills** → **Resident Skills**
2. Click **"Add Skill to Resident"**
3. Select resident: "Juan Dela Cruz"
4. Select skill: "Carpentry"
5. Set proficiency: "Advanced"
6. Enter years of experience: "8"
7. Add certification: "TESDA Certificate 2020"
8. Click **"Add Skill"**

### Example 2: Creating and Managing a Training Program

1. Go to **Livelihood & Skills** → **Livelihood Programs**
2. Click **"Add Program"**
3. Fill in details:
   - Program Name: "Carpentry Skills Development"
   - Type: "Skills Development"
   - Start Date: 2025-02-01
   - Target Beneficiaries: 20
   - Budget: 50,000
4. Click **"Add Program"**
5. Go to **Training Sessions**
6. Click **"Add Training Session"**
7. Select the program and add session details
8. Click **"Enroll Participant"** to add residents
9. Track attendance and completion status

### Example 3: Posting and Managing Job Opportunities

1. Go to **Job Opportunities**
2. Click **"Post Job Opportunity"**
3. Fill in job details:
   - Job Title: "Carpenter - Construction Company"
   - Required Skills: "Carpentry, Welding, Safety"
   - Employer: "ABC Construction"
   - Employment Type: "Full-time"
   - Salary: "18,000 - 22,000"
4. Click **"Post Opportunity"**
5. Residents can apply through the system
6. Review applications and update status
7. Track hired candidates

### Example 4: Generating Reports

1. Go to **Livelihood Reports**
2. Select report type:
   - **Skills Summary:** See which skills are most common and proficiency distribution
   - **Programs:** View participation and completion rates
   - **Training:** Track training session metrics
   - **Jobs:** Monitor job posting success
   - **Residents:** Identify skilled residents for opportunities

## Key Features & Benefits

### For Barangay Administration
- **Economic Planning:** Data-driven insights for economic development initiatives
- **Resource Allocation:** Identify skill gaps and training needs
- **Program Monitoring:** Track livelihood program effectiveness
- **Reporting:** Generate comprehensive statistics for stakeholders

### For Residents
- **Skill Documentation:** Formal record of expertise and certifications
- **Training Opportunities:** Easy access to skill development programs
- **Job Matching:** Connection to employment opportunities matching their skills
- **Career Development:** Track progress and certifications

### For Employers
- **Talent Pool:** Access to pre-screened, skilled residents
- **Skill Verification:** Confirmed skill levels and certifications
- **Reduced Recruitment Time:** Direct connection to qualified candidates

## Pre-loaded Sample Data

The system includes 18 pre-loaded skills across 9 categories:

**Construction:** Carpentry, Plumbing, Electrical Work, Welding, Masonry
**Hospitality:** Cooking, Baking
**Tailoring:** Sewing
**Beauty:** Hairdressing, Beautician
**IT:** Computer Basics, Web Design, Graphic Design
**Farming:** Agriculture, Livestock Raising
**Mechanics:** Automotive Repair
**Creative:** Photography
**Finance:** Accounting

## Activity Logging

All actions in the Livelihood & Skills module are logged for audit purposes:
- Skill additions/updates/deletions
- Program creation and updates
- Training session management
- Participant enrollment and status updates
- Job posting and application management

## Best Practices

1. **Regular Updates:** Keep resident skills current as they complete training
2. **Certification Tracking:** Always record certifications for verification
3. **Program Coordination:** Assign clear coordinators for each program
4. **Attendance Tracking:** Maintain accurate attendance records for training
5. **Job Matching:** Regularly review job opportunities and match with skilled residents
6. **Report Review:** Generate reports monthly to monitor progress

## Troubleshooting

### Issue: Cannot add skill to resident
**Solution:** Ensure resident is marked as "Active" status in the system

### Issue: Training session not showing participants
**Solution:** Check that participants have been enrolled in the session through the "Enroll Participant" button

### Issue: Job opportunity not appearing
**Solution:** Verify the opportunity status is "Open" and deadline hasn't passed

## Support & Maintenance

For issues or feature requests related to the Livelihood & Skills Registry:
1. Check Activity Logs for recent changes
2. Verify database integrity
3. Contact system administrator

## Future Enhancements

Potential features for future versions:
- Skill endorsements from employers
- Microfinance loan tracking
- Skills marketplace
- Mobile app for residents
- Integration with DOLE systems
- Advanced analytics and predictive modeling
