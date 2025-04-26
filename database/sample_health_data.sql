-- Sample data for health_information table

-- Clear existing data
TRUNCATE TABLE `health_information`;

-- Insert Heart Disease Facts (category 1)
INSERT INTO `health_information` (`title`, `detail`, `category`, `index`) VALUES
('Heart Disease is the Leading Cause of Death', 'Heart disease is the leading cause of death for both men and women in many countries. About 655,000 Americans die from heart disease each year—that's 1 in every 4 deaths.', 1, 1),
('Risk Factors', 'Common risk factors include high blood pressure, high cholesterol, smoking, diabetes, obesity, poor diet, physical inactivity, and excessive alcohol use.', 1, 2),
('Types of Heart Disease', 'There are many types of heart disease, including coronary artery disease, heart failure, arrhythmias, valve disease, and congenital heart defects.', 1, 3),
('Silent Symptoms', 'Heart disease can sometimes be "silent" and not diagnosed until a person experiences signs or symptoms of a heart attack, heart failure, or arrhythmia.', 1, 4);

-- Insert Prevention Tips (category 2)
INSERT INTO `health_information` (`title`, `detail`, `category`, `index`) VALUES
('Healthy Diet', 'Eat a diet rich in fruits, vegetables, whole grains, and lean proteins. Limit saturated fats, trans fats, sodium, and added sugars.', 2, 1),
('Regular Exercise', 'Aim for at least 150 minutes of moderate-intensity aerobic activity or 75 minutes of vigorous activity each week, plus muscle-strengthening activities at least twice a week.', 2, 2),
('Quit Smoking', 'Smoking damages the heart and blood vessels. Quitting smoking reduces your risk of heart disease significantly, even if you've been smoking for years.', 2, 3),
('Manage Stress', 'Chronic stress can contribute to heart disease. Practice stress-reduction techniques such as deep breathing, meditation, yoga, or tai chi.', 2, 4),
('Regular Check-ups', 'Regular health screenings can detect risk factors early. Monitor your blood pressure, cholesterol levels, and blood sugar regularly.', 2, 5);

-- Insert Treatment Options (category 3)
INSERT INTO `health_information` (`title`, `detail`, `category`, `index`) VALUES
('Medications', 'Various medications can help treat heart disease, including statins to lower cholesterol, blood pressure medications, blood thinners, and more.', 3, 1),
('Lifestyle Changes', 'Doctors often recommend lifestyle changes as the first line of treatment, including diet improvements, increased physical activity, and smoking cessation.', 3, 2),
('Surgical Procedures', 'Procedures such as angioplasty, stent placement, or bypass surgery may be necessary to improve blood flow to the heart.', 3, 3),
('Cardiac Rehabilitation', 'Cardiac rehab programs help people recover from heart attacks or surgery through supervised exercise, education, and support.', 3, 4),
('Implantable Devices', 'Devices such as pacemakers or implantable cardioverter defibrillators (ICDs) may be used to help control abnormal heart rhythms.', 3, 5);