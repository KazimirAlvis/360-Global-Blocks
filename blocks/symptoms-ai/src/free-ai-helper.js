// Free AI content generation using Hugging Face
async function generateFreeContent(symptom) {
	try {
		// Try Hugging Face free API first
		const hfResponse = await fetch('https://api-inference.huggingface.co/models/microsoft/DialoGPT-medium', {
			method: 'POST',
			headers: {
				Authorization: 'Bearer YOUR_FREE_HF_TOKEN',
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				inputs: `Generate medical information about ${symptom}. Include causes, symptoms, and when to see a doctor.`,
				parameters: {
					max_new_tokens: 200,
					temperature: 0.7,
				},
			}),
		});

		if (hfResponse.ok) {
			return await hfResponse.json();
		}

		// Fallback to predefined templates
		return generateTemplateContent(symptom);
	} catch (error) {
		console.error('AI generation failed:', error);
		return generateTemplateContent(symptom);
	}
}

function generateTemplateContent(symptom) {
	const templates = {
		'knee pain': {
			causes: 'Common causes include injury, arthritis, overuse, or underlying conditions.',
			symptoms: 'Pain, swelling, stiffness, difficulty moving the knee.',
			treatment: 'Rest, ice, compression, elevation (RICE). See a doctor if severe.',
			when_to_see_doctor: 'Persistent pain, severe swelling, inability to bear weight.',
		},
		headache: {
			causes: 'Stress, dehydration, eye strain, sinus issues, or medical conditions.',
			symptoms: 'Head pain, sensitivity to light, nausea, tension.',
			treatment: 'Rest, hydration, over-the-counter pain relief.',
			when_to_see_doctor: 'Sudden severe headache, fever, vision changes.',
		},
		// Add more templates...
	};

	const template = templates[symptom.toLowerCase()] || {
		causes: 'Various factors can contribute to this condition.',
		symptoms: 'Symptoms may vary depending on the underlying cause.',
		treatment: 'Treatment options should be discussed with a healthcare provider.',
		when_to_see_doctor: 'Consult a healthcare provider for proper evaluation.',
	};

	return {
		content: `
            <h3>Understanding ${symptom}</h3>
            <h4>Common Causes:</h4>
            <p>${template.causes}</p>
            <h4>Typical Symptoms:</h4>
            <p>${template.symptoms}</p>
            <h4>Basic Care:</h4>
            <p>${template.treatment}</p>
            <h4>When to See a Doctor:</h4>
            <p>${template.when_to_see_doctor}</p>
        `,
	};
}
