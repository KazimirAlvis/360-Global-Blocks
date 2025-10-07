#!/bin/bash

# Create the healthicons directory if it doesn't exist
mkdir -p assets/healthicons

# Base URL for Health Icons
BASE_URL="https://healthicons.org/icons/svg/filled"

# Body icons
echo "Downloading body icons..."
curl -s "${BASE_URL}/body/heart-organ.svg" -o assets/healthicons/heart_organ.svg
curl -s "${BASE_URL}/body/lungs.svg" -o assets/healthicons/lungs.svg
curl -s "${BASE_URL}/body/neurology.svg" -o assets/healthicons/brain.svg
curl -s "${BASE_URL}/body/eye.svg" -o assets/healthicons/eye.svg
curl -s "${BASE_URL}/body/ear.svg" -o assets/healthicons/ear.svg
curl -s "${BASE_URL}/body/tooth.svg" -o assets/healthicons/tooth.svg
curl -s "${BASE_URL}/body/kidneys.svg" -o assets/healthicons/kidneys.svg
curl -s "${BASE_URL}/body/liver.svg" -o assets/healthicons/liver.svg
curl -s "${BASE_URL}/body/stomach.svg" -o assets/healthicons/stomach.svg
curl -s "${BASE_URL}/body/spine.svg" -o assets/healthicons/spine.svg

# Conditions icons  
echo "Downloading conditions icons..."
curl -s "${BASE_URL}/conditions/allergies.svg" -o assets/healthicons/allergies.svg
curl -s "${BASE_URL}/conditions/headache.svg" -o assets/healthicons/headache.svg
curl -s "${BASE_URL}/conditions/fever.svg" -o assets/healthicons/fever.svg
curl -s "${BASE_URL}/conditions/coughing-alt.svg" -o assets/healthicons/coughing.svg
curl -s "${BASE_URL}/conditions/diarrhea.svg" -o assets/healthicons/diarrhea.svg
curl -s "${BASE_URL}/conditions/nausea.svg" -o assets/healthicons/nausea.svg
curl -s "${BASE_URL}/conditions/back-pain.svg" -o assets/healthicons/back_pain.svg
curl -s "${BASE_URL}/conditions/diabetes.svg" -o assets/healthicons/diabetes.svg
curl -s "${BASE_URL}/conditions/overweight.svg" -o assets/healthicons/overweight.svg
curl -s "${BASE_URL}/conditions/underweight.svg" -o assets/healthicons/underweight.svg

# Devices icons
echo "Downloading devices icons..."
curl -s "${BASE_URL}/devices/stethoscope.svg" -o assets/healthicons/stethoscope.svg
curl -s "${BASE_URL}/devices/syringe.svg" -o assets/healthicons/syringe.svg
curl -s "${BASE_URL}/devices/thermometer-digital.svg" -o assets/healthicons/thermometer_digital.svg
curl -s "${BASE_URL}/devices/blood-pressure_monitor.svg" -o assets/healthicons/blood_pressure.svg
curl -s "${BASE_URL}/devices/microscope.svg" -o assets/healthicons/microscope.svg
curl -s "${BASE_URL}/devices/wheelchair.svg" -o assets/healthicons/wheelchair.svg
curl -s "${BASE_URL}/devices/xray.svg" -o assets/healthicons/xray.svg
curl -s "${BASE_URL}/devices/ultrasound-scanner.svg" -o assets/healthicons/ultrasound.svg
curl -s "${BASE_URL}/devices/defibrillator.svg" -o assets/healthicons/defibrillator.svg
curl -s "${BASE_URL}/devices/cpap-machine.svg" -o assets/healthicons/cpap_machine.svg

# People icons
echo "Downloading people icons..."
curl -s "${BASE_URL}/people/doctor.svg" -o assets/healthicons/doctor.svg
curl -s "${BASE_URL}/people/nurse.svg" -o assets/healthicons/nurse.svg
curl -s "${BASE_URL}/people/doctor-female.svg" -o assets/healthicons/doctor_female.svg
curl -s "${BASE_URL}/people/doctor-male.svg" -o assets/healthicons/doctor_male.svg
curl -s "${BASE_URL}/people/elderly.svg" -o assets/healthicons/elderly.svg
curl -s "${BASE_URL}/people/pregnant.svg" -o assets/healthicons/pregnant.svg
curl -s "${BASE_URL}/people/baby-0203m.svg" -o assets/healthicons/baby.svg
curl -s "${BASE_URL}/people/community-healthworker.svg" -o assets/healthicons/community_health_worker.svg
curl -s "${BASE_URL}/people/emergency-operations_center.svg" -o assets/healthicons/emergency_worker.svg
curl -s "${BASE_URL}/people/person.svg" -o assets/healthicons/person.svg

# Specialties icons
echo "Downloading specialties icons..."
curl -s "${BASE_URL}/specialties/cardiology.svg" -o assets/healthicons/cardiology.svg
curl -s "${BASE_URL}/specialties/neurology.svg" -o assets/healthicons/neurology.svg
curl -s "${BASE_URL}/specialties/pediatrics.svg" -o assets/healthicons/pediatrics.svg
curl -s "${BASE_URL}/specialties/orthopedics.svg" -o assets/healthicons/orthopedics.svg
curl -s "${BASE_URL}/specialties/radiology.svg" -o assets/healthicons/radiology.svg
curl -s "${BASE_URL}/specialties/pharmacy.svg" -o assets/healthicons/pharmacy.svg
curl -s "${BASE_URL}/specialties/psychology.svg" -o assets/healthicons/psychology.svg
curl -s "${BASE_URL}/specialties/physical-therapy.svg" -o assets/healthicons/physical_therapy.svg
curl -s "${BASE_URL}/specialties/emergency-department.svg" -o assets/healthicons/emergency_department.svg
curl -s "${BASE_URL}/specialties/intensive-care_unit.svg" -o assets/healthicons/intensive_care.svg

echo "Download complete!"
