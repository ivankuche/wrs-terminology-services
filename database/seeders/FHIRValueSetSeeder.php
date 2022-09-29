<?php

namespace Database\Seeders;

use App\Models\FHIRValueSet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FHIRValueSetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $valuesets= [
            "IdentifierUse"=>"https://build.fhir.org/codesystem-identifier-use.json",
            "IdentifierType"=>"https://terminology.hl7.org/3.1.0/CodeSystem-v2-0203.json",
            "NameUse"=>"https://hl7.org/fhir/R4/codesystem-name-use.json",
            "ContactPointSystem"=>"https://build.fhir.org/codesystem-contact-point-system.json",
            "ContactPointUse"=>"https://build.fhir.org/codesystem-contact-point-use.json",
            "AdministrativeGender"=>"https://build.fhir.org/codesystem-administrative-gender.json",
            "AddressUse"=>"https://build.fhir.org/codesystem-address-use.json",
            "AddressType"=>"https://build.fhir.org/codesystem-address-type.json",
            "MaritalStatus"=>["https://terminology.hl7.org/3.1.0/CodeSystem-v3-MaritalStatus.json","https://terminology.hl7.org/3.1.0/CodeSystem-v3-NullFlavor.json"],
            "LinkType"=>"https://build.fhir.org/codesystem-link-type.json",
            "ProvenanceActivityType"=>["https://hl7.org/fhir/us/core/STU5/CodeSystem-us-core-provenance-participant-type.json","https://terminology.hl7.org/3.1.0/CodeSystem-provenance-participant-type.json"],
            "ProvenanceEntityRole"=>"https://build.fhir.org/codesystem-provenance-entity-role.json",
            "SecurityRoleType"=>"https://build.fhir.org/codesystem-sample-security-structural-roles.json",
            "PatientContactRelationship"=>"https://terminology.hl7.org/3.1.0/CodeSystem-v2-0131.json",
            "ResourceType"=>"https://build.fhir.org/codesystem-resource-types.json",
            "AllergyIntoleranceClinicalStatusCodes"=>"https://terminology.hl7.org/3.1.0/CodeSystem-allergyintolerance-clinical.json",
            "AllergyIntoleranceVerificationStatus"=>"https://terminology.hl7.org/3.1.0/CodeSystem-allergyintolerance-verification.json",
            "AllergyIntoleranceType"=>"https://build.fhir.org/codesystem-allergy-intolerance-type.json",
            "AllergyIntoleranceCategory"=>"https://build.fhir.org/codesystem-allergy-intolerance-category.json",
            "AllergyIntoleranceCriticality"=>"https://build.fhir.org/codesystem-allergy-intolerance-criticality.json",
            "AllergyIntoleranceSeverity"=>"https://build.fhir.org/codesystem-reaction-event-severity.json",
            "BirthSex"=>"https://terminology.hl7.org/3.1.0/CodeSystem-v3-AdministrativeGender.json",
            "RequestStatus"=>"https://build.fhir.org/codesystem-request-status.json",
            "CarePlanIntent"=>"https://www.hl7.org/fhir/codesystem-request-intent.json",
            "CarePlanActivityKind"=>null,
            "CarePlanActivityStatus"=>"https://build.fhir.org/codesystem-care-plan-activity-status.json",
            "CareTeamStatus"=>"https://build.fhir.org/codesystem-care-team-status.json",
            "ConditionClinicalStatusCodes"=>"https://terminology.hl7.org/3.1.0/CodeSystem-condition-clinical.json",
            "ConditionVerificationStatus"=>"https://build.fhir.org/codesystem-condition-ver-status.json",
            "UDIEntryType"=>"https://build.fhir.org/codesystem-udi-entry-type.json",
            "DeviceNameType"=>"https://build.fhir.org/codesystem-device-nametype.json",
            "FHIRDeviceStatus"=>"https://build.fhir.org/codesystem-device-status.json",
            "FHIRDeviceStatusReason"=>"https://fhir-ru.github.io/codesystem-device-status-reason.json",
            "FHIRDeviceSpecializationCategory"=>"https://build.fhir.org/codesystem-device-specialization-category.json",
            "FHIRDeviceOperationalStatus"=>"https://build.fhir.org/codesystem-device-operationalstatus.json",
            "DeviceRelationType"=>"https://build.fhir.org/codesystem-device-relationtype.json",
            "DiagnosticReportStatus"=>"https://build.fhir.org/codesystem-diagnostic-report-status.json",
            "USCoreProvenancePaticipantTypeCodes"=>["https://hl7.org/fhir/us/core/STU4/CodeSystem-us-core-provenance-participant-type.json","https://terminology.hl7.org/1.0.0//CodeSystem-provenance-participant-type.json"],
            "DocumentReferenceStatus"=>"https://build.fhir.org/codesystem-document-reference-status.json",
            "CompositionStatus"=>"https://build.fhir.org/codesystem-composition-status.json",
            "USCoreDocumentReferenceCategory"=>"https://hl7.org/fhir/us/core/STU4/CodeSystem-us-core-documentreference-category.json",
            "v3CodeSystemActCode"=>"https://terminology.hl7.org/3.1.0/CodeSystem-v3-ActCode.json",
            "DocumentAttestationMode"=>"https://build.fhir.org/codesystem-composition-attestation-mode.json",
            "DocumentRelationshipType"=>"https://build.fhir.org/codesystem-document-relationship-type.json",
            "DocumentReferenceFormatCodeSet"=>"https://profiles.ihe.net/fhir/ihe.formatcode.fhir/1.1.0/CodeSystem-formatcode.json",
            "GoalAchievementStatus"=>"https://terminology.hl7.org/3.1.0/CodeSystem-goal-achievement.json",
            "GoalCategory"=>"https://terminology.hl7.org/3.1.0/CodeSystem-goal-category.json",
            "GoalPriority"=>"https://terminology.hl7.org/3.1.0/CodeSystem-goal-priority.json",
            "ImmunizationSubpotentReason"=>"https://terminology.hl7.org/3.1.0/CodeSystem-immunization-subpotent-reason.json",
            "ImmunizationProgramEligibility"=>"https://terminology.hl7.org/3.1.0/CodeSystem-immunization-program-eligibility.json",
            "ImmunizationFundingSource"=>"https://terminology.hl7.org/3.1.0/CodeSystem-immunization-funding-source.json",
            "medicationRequestStatus"=>"https://build.fhir.org/codesystem-medicationrequest-status.json",
            "medicationRequestStatusReasonCodes"=>"https://terminology.hl7.org/3.1.0/CodeSystem-medicationrequest-status-reason.json",
            "medicationRequestIntent"=>"https://build.fhir.org/codesystem-medicationrequest-intent.json",
            "medicationRequestAdministrationLocationCodes"=>"https://terminology.hl7.org/3.1.0/CodeSystem-medicationrequest-admin-location.json",
            "RequestPriority"=>"https://build.fhir.org/codesystem-request-priority.json",
            "MedicationIntendedPerformerRole"=>"https://build.fhir.org/codesystem-medication-intended-performer-role.json",
            "DoseAndRateType"=>"https://build.fhir.org/codesystem-dose-rate-type.json",
            "MedicationDoseAids"=>"https://build.fhir.org/codesystem-medication-dose-aid.json",
            "MedicationStatusCodes"=>"https://build.fhir.org/codesystem-medication-status.json",
            "MedicationIngredientStrengthCodes"=>"https://build.fhir.org/codesystem-medication-ingredientstrength.json",
            "triggeredBytype"=>"https://build.fhir.org/codesystem-observation-triggeredbytype.json",
            "ObservationStatus"=>"https://build.fhir.org/codesystem-observation-status.json",
            "ObservationCategoryCodes"=>"https://terminology.hl7.org/3.1.0/CodeSystem-observation-category.json",
            "DataAbsentReason"=>"https://build.fhir.org/codesystem-data-absent-reason.json",
            "ObservationInterpretationCodes"=>"https://terminology.hl7.org/3.1.0/CodeSystem-v3-ObservationInterpretation.json",
            "ObservationReferenceRangeNormalValueCodes"=>"https://build.fhir.org/codesystem-observation-referencerange-normalvalue.json",
            "ObservationReferenceRangeMeaningCodes"=>"https://terminology.hl7.org/3.1.0/CodeSystem-referencerange-meaning.json",
            "EventStatus"=>"https://www.hl7.org/fhir/codesystem-event-status.json",
            "EncounterStatus"=>"https://www.hl7.org/fhir/codesystem-encounter-status.json",
            "Encountertype"=>"https://www.hl7.org/fhir/codesystem-encounter-type.json",
            "ServiceType"=>"https://www.hl7.org/fhir/codesystem-service-type.json",
            "v3CodeSystemActPriority"=>"https://www.hl7.org/fhir/v3/ActPriority/v3-ActPriority.cs.json",
            "DiagnosisRole"=>"https://www.hl7.org/fhir/codesystem-diagnosis-role.json",
            "AdmitSource"=>"https://www.hl7.org/fhir/codesystem-encounter-admit-source.json",
            "hl7VS-re-admissionIndicator"=>"https://terminology.hl7.org/3.1.0/CodeSystem-v2-0092.json",
            "Diet"=>"https://www.hl7.org/fhir/codesystem-encounter-diet.json",
            "SpecialArrangements"=>"https://www.hl7.org/fhir/codesystem-encounter-special-arrangements.json",
            "DischargeDisposition"=>"https://www.hl7.org/fhir/codesystem-encounter-discharge-disposition.json",
            "EncounterLocationStatus"=>"https://www.hl7.org/fhir/codesystem-encounter-location-status.json",
            "LocationType"=>"https://www.hl7.org/fhir/codesystem-location-physical-type.json",
            "OrganizationType"=>"https://www.hl7.org/fhir/codesystem-organization-type.json",
            "ContactEntityType"=>"https://www.hl7.org/fhir/codesystem-contactentity-type.json",
            "v2Table0360Version2.7"=>"https://www.hl7.org/fhir/v2/0360/2.7/v2-0360-2.7.cs.json",

            // Seguir
            "CarePlanActivityStatusReason"=>["https://terminology.hl7.org/3.1.0/CodeSystem-medicationrequest-status-reason.json",]
        ];

        $valueSet= new FHIRValueSet();

        foreach ($valuesets as $key=>$value)
            $valueSet->create([
                "name"=>$key,
                "url"=>json_encode($value)
            ]);
    }
}
