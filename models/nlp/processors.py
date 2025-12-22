"""
Text Processing Utilities
Handles text preprocessing, keyword extraction, and summary generation
"""

import re
from collections import Counter
from datetime import datetime
try:
    from typing import List, Dict, Any
except ImportError:
    # Python < 3.5 compatibility
    pass


class TextProcessor:
    """Text preprocessing and basic analysis"""
    
    def preprocess(self, text: str) -> Dict[str, Any]:
        """Preprocess text and extract basic statistics"""
        text_clean = text.strip()
        
        # Split into sentences
        sentences = [s.strip() for s in re.split(r'[.!?]+', text_clean) if s.strip()]
        
        # Split into words
        words = text_clean.lower().split()
        words = [re.sub(r'[^\w]', '', word) for word in words if word]
        
        return {
            'original': text,
            'cleaned': text_clean,
            'sentences': sentences,
            'words': words,
            'word_count': len(words),
            'sentence_count': len(sentences)
        }
    
    def extract_keywords(self, words: List[str], top_n: int = 15) -> List[str]:
        """Extract significant keywords"""
        # Common stop words
        stop_words = {
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to',
            'for', 'of', 'with', 'by', 'as', 'is', 'are', 'was', 'were',
            'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does',
            'did', 'will', 'would', 'should', 'could', 'can', 'may',
            'might', 'must', 'shall', 'this', 'that', 'these', 'those',
            'patient', 'patients', 'doctor', 'dr', 'hospital', 'clinic',
            'said', 'says', 'report', 'note', 'history', 'presented',
            'complains', 'complaining', 'shows', 'showing'
        }
        
        # Filter words
        filtered = [w for w in words if w not in stop_words and len(w) > 2 and w.isalpha()]
        
        if not filtered:
            return ["No significant keywords found"]
        
        # Count frequency
        word_counts = Counter(filtered)
        
        # Get top keywords
        keywords = []
        for word, count in word_counts.most_common(top_n):
            keywords.append(f"{word.title()} ({count}x)")
        
        return keywords
    
    def get_timestamp(self) -> str:
        """Get current timestamp"""
        return datetime.now().strftime('%Y-%m-%d %H:%M:%S')


class SummaryGenerator:
    """Generate clinical summaries"""
    
    def generate(self, sentences: List[str], full_text: str) -> str:
        """Generate a concise clinical summary"""
        if not sentences:
            return full_text[:300] + "..." if len(full_text) > 300 else full_text
        
        # Key phrases that indicate important information
        key_phrases = [
            'diagnosis', 'diagnosed', 'presented with', 'complains of',
            'symptoms', 'treatment', 'prescribed', 'recommended',
            'advised', 'findings', 'assessment', 'plan', 'prognosis',
            'follow up', 'follow-up', 'discharge', 'admission'
        ]
        
        # Find important sentences
        important_sentences = []
        for sentence in sentences:
            sentence_lower = sentence.lower()
            # Check for key phrases
            if any(phrase in sentence_lower for phrase in key_phrases):
                important_sentences.append(sentence)
        
        # Build summary
        if important_sentences:
            summary = " ".join(important_sentences[:3])
        elif len(sentences) >= 3:
            # Use first and last sentences
            summary = sentences[0] + " " + sentences[-1]
        else:
            # Use all sentences
            summary = " ".join(sentences)
        
        # Trim if too long
        if len(summary) > 400:
            summary = summary[:397] + "..."
        
        return summary

