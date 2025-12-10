from flask import Flask, render_template, request, redirect, url_for, session, jsonify, flash, send_from_directory
import sqlite3
import os
from werkzeug.utils import secure_filename
from werkzeug.security import generate_password_hash, check_password_hash

# Import the NLP class
try:
    from models.simple_nlp import SimpleNLP
    nlp = SimpleNLP()  # Initialize NLP
except ImportError:
    print("Warning: NLP module not found. Creating placeholder.")
    nlp = None

app = Flask(__name__)
app.secret_key = "your-secret-key-here-change-this"

# ========== YOUR ORIGINAL DATABASE CODE ==========
def get_db():
    conn = sqlite3.connect("database.db")
    conn.row_factory = sqlite3.Row
    return conn

# Create tables if not exists
with get_db() as conn:
    conn.execute('''CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT UNIQUE NOT NULL,
                    password TEXT NOT NULL,
                    role TEXT NOT NULL)''')
    
    conn.execute('''CREATE TABLE IF NOT EXISTS uploads (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        filename TEXT NOT NULL,
                        uploader TEXT NOT NULL,
                        role TEXT NOT NULL,
                        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )''')

# ========== ALL YOUR ORIGINAL ROUTES ==========
@app.route('/')
def home():
    return render_template('homepage.html')

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']

        conn = get_db()
        user = conn.execute("SELECT * FROM users WHERE username=?", (username,)).fetchone()
        conn.close()

        if user and check_password_hash(user['password'], password):
            session['username'] = user['username']
            session['role'] = user['role']

            if user['role'] == 'admin':
                return redirect(url_for('admin_dashboard'))
            elif user['role'] == 'doctor':
                return redirect(url_for('doctor_dashboard'))
            else:
                return redirect(url_for('doctor_dashboard'))
        else:
            return render_template('login.html', error="Invalid username or password")

    return render_template('login.html')

@app.route('/signup', methods=['GET', 'POST'])
def signup():
    if request.method == 'POST':
        username = request.form['username'].strip()
        password = request.form['password']
        role = request.form['role']

        if role not in ['doctor', 'nurse', 'admin']:
            return render_template('signup.html', error="Invalid role selected!")

        hashed_password = generate_password_hash(password)

        try:
            with get_db() as conn:
                conn.execute("INSERT INTO users (username, password, role) VALUES (?, ?, ?)",
                             (username, hashed_password, role))
            return redirect(url_for('login'))
        except sqlite3.IntegrityError:
            return render_template('signup.html', error="Username already exists!")

    return render_template('signup.html')

@app.route('/doctor_dashboard')
def doctor_dashboard():
    if session.get('role') in ['doctor', 'nurse']:
        can_edit = (session.get('role') == 'doctor')
        return render_template(
            'doctor_dashboard.html',
            username=session['username'],
            role=session['role'],
            can_edit=can_edit
        )
    return redirect(url_for('login'))

UPLOAD_FOLDER = 'uploads'
os.makedirs(UPLOAD_FOLDER, exist_ok=True)
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER

os.makedirs(os.path.join(UPLOAD_FOLDER, 'doctors'), exist_ok=True)
os.makedirs(os.path.join(UPLOAD_FOLDER, 'nurses'), exist_ok=True)

@app.route('/upload', methods=['GET', 'POST'])
def upload():
    if session.get('role') not in ['doctor', 'nurse']:
        return redirect(url_for('login'))

    if request.method == 'POST':
        if 'file' in request.files and request.files['file'].filename != '':
            file = request.files['file']
            filename = secure_filename(file.filename)

            role_folder = 'doctors' if session['role'] == 'doctor' else 'nurses'
            folder_path = os.path.join(app.config['UPLOAD_FOLDER'], role_folder)
            filepath = os.path.join(folder_path, filename)
            file.save(filepath)

            with get_db() as conn:
                conn.execute(
                    "INSERT INTO uploads (filename, uploader, role) VALUES (?, ?, ?)",
                    (filename, session['username'], session['role'])
                )
            flash("File uploaded successfully ✅", "success")
            return redirect(url_for('view_uploads'))

    return render_template('upload.html')

@app.route('/view_uploads')
def view_uploads():
    role = session.get('role')
    username = session.get('username')

    if role not in ['doctor', 'nurse']:
        return redirect(url_for('login'))

    with get_db() as conn:
        if role == 'doctor':
            # Doctor sees all files
            files = conn.execute(
                "SELECT * FROM uploads ORDER BY uploaded_at DESC"
            ).fetchall()
        else:
            # Nurse sees: ALL doctor files + their own nurse files
            files = conn.execute(
                """SELECT * FROM uploads 
                   WHERE role = 'doctor' OR (uploader = ? AND role = ?)
                   ORDER BY uploaded_at DESC""",
                (username, role)
            ).fetchall()

    return render_template('view_uploads.html', files=files, role=role)

@app.route('/download/<filename>/<uploader_role>')
def download_file(filename, uploader_role):
    role = session.get('role')
    if role not in ['doctor', 'nurse']:
        return redirect(url_for('login'))

    folder_name = 'doctors' if uploader_role == 'doctor' else 'nurses'
    folder_path = os.path.join(app.config['UPLOAD_FOLDER'], folder_name)

    # Allow nurses to download doctor files too
    if role == 'nurse':
        # Nurses can download: doctor files OR their own nurse files
        if uploader_role == 'nurse':
            # Check if it's their own file
            with get_db() as conn:
                file_record = conn.execute(
                    "SELECT uploader FROM uploads WHERE filename=? AND role=?",
                    (filename, uploader_role)
                ).fetchone()
                
                if file_record and file_record['uploader'] != session['username']:
                    return "Unauthorized: This is another nurse's file", 403
        # Doctor files are always allowed for nurses
    
    return send_from_directory(folder_path, filename, as_attachment=True)

@app.route('/upload_note', methods=['POST'])
def upload_note():
    if session.get('role') not in ['doctor', 'nurse']:
        return redirect(url_for('login'))

    content = request.form.get('note_content')
    filename_input = request.form.get('note_filename').strip()
    filename = secure_filename(filename_input if filename_input else "note") + ".txt"

    role_folder = 'doctors' if session['role'] == 'doctor' else 'nurses'
    folder_path = os.path.join(app.config['UPLOAD_FOLDER'], role_folder)
    filepath = os.path.join(folder_path, filename)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    with get_db() as conn:
        conn.execute(
            "INSERT INTO uploads (filename, uploader, role) VALUES (?, ?, ?)",
            (filename, session['username'], session['role'])
        )

    flash("Note saved successfully ✅", "success")
    return redirect(url_for('view_uploads'))

@app.route('/edit/<filename>/<uploader_role>', methods=['GET', 'POST'])
def edit_file(filename, uploader_role):
    if session.get('role') != 'doctor':
        return redirect(url_for('login'))

    folder_name = 'doctors' if uploader_role == 'doctor' else 'nurses'
    folder_path = os.path.join(app.config['UPLOAD_FOLDER'], folder_name)
    filepath = os.path.join(folder_path, filename)

    if not os.path.exists(filepath) or not filename.endswith('.txt'):
        return "File not found or not editable.", 404

    if request.method == 'POST':
        new_content = request.form.get('edited_content')
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        flash("File updated successfully ✅", "success")
        return redirect(url_for('view_uploads'))

    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    return render_template('edit_file.html', filename=filename, content=content)

@app.route('/delete/<filename>/<uploader_role>', methods=['POST'])
def delete_file(filename, uploader_role):
    if session.get('role') != 'doctor':
        return redirect(url_for('login'))

    folder_name = 'doctors' if uploader_role == 'doctor' else 'nurses'
    folder_path = os.path.join(app.config['UPLOAD_FOLDER'], folder_name)
    filepath = os.path.join(folder_path, filename)

    try:
        if os.path.exists(filepath):
            os.remove(filepath)
    except Exception as e:
        print(f"Error deleting file: {e}")

    with get_db() as conn:
        conn.execute("DELETE FROM uploads WHERE filename=? AND role=?", (filename, uploader_role))
        conn.commit()

    flash('File deleted successfully ✅', 'success')
    return redirect(url_for('view_uploads'))

@app.route('/settings', methods=['GET', 'POST'])
def settings():
    if session.get('role') not in ['doctor', 'nurse']:
        return redirect(url_for('login'))

    if request.method == 'POST':
        new_password = request.form.get('new_password')
        if new_password:
            hashed_password = generate_password_hash(new_password)
            with get_db() as conn:
                conn.execute(
                    "UPDATE users SET password=? WHERE username=?",
                    (hashed_password, session['username'])
                )
            flash('Password updated successfully ✅', 'success')
            return redirect(url_for('settings'))

    return render_template('settings.html', username=session['username'])

@app.route('/logout')
def logout():
    session.clear()
    return redirect(url_for('login'))

@app.route('/admin_dashboard')
def admin_dashboard():
    if session.get('role') == 'admin':
        conn = get_db()
        users = conn.execute("SELECT * FROM users").fetchall()
        total_users = conn.execute("SELECT COUNT(*) FROM users").fetchone()[0]

        conn.close()
        return render_template('admin_dashboard.html',
                               username=session['username'],
                               users=users,total_users=total_users)
    return redirect(url_for('login'))

@app.route('/admin-users')
def admin_users():
    conn = get_db()
    users = conn.execute("SELECT * FROM users").fetchall()
    conn.close()
    return render_template('admin-users.html', users=users)

@app.route('/admin-notes')
def admin_notes():
    return render_template('admin-notes.html')

@app.route('/admin/add_user', methods=['POST'])
def add_user():
    data = request.get_json()
    username = data.get('username')
    password = data.get('password')
    role = data.get('role')

    conn = get_db()
    try:
        conn.execute("INSERT INTO users (username, password, role) VALUES (?, ?, ?)",
                     (username, password, role))
        conn.commit()
        return jsonify({'message': 'User added successfully'})
    except sqlite3.IntegrityError:
        return jsonify({'error': 'Username already exists'})
    finally:
        conn.close()

@app.route('/admin/edit_user/<int:user_id>', methods=['POST'])
def edit_user(user_id):
    if session.get('role') != 'admin':
        return jsonify({'error': 'Unauthorized'}), 403

    data = request.get_json()
    username = data.get('username')
    role = data.get('role')

    if not username or not role:
        return jsonify({'error': 'All fields are required'}), 400

    conn = get_db()
    try:
        conn.execute("UPDATE users SET username = ?, role = ? WHERE id = ?", (username, role, user_id))
        conn.commit()
        return jsonify({'message': 'User updated successfully'})
    except sqlite3.IntegrityError:
        return jsonify({'error': 'Username already exists'}), 409
    finally:
        conn.close()

@app.route('/admin/delete_user/<int:user_id>', methods=['POST'])
def delete_user(user_id):
    if session.get('role') != 'admin':
        return jsonify({'error': 'Unauthorized'})

    conn = get_db()
    conn.execute("DELETE FROM users WHERE id=?", (user_id,))
    conn.commit()
    conn.close()
    return jsonify({'message': 'User deleted successfully'})

# ========== ENHANCED NLP ANALYSIS ROUTE ==========
@app.route('/analyze_document', methods=['GET', 'POST'])
def analyze_document():
    """Working NLP analysis for uploaded files"""
    if 'username' not in session:
        return redirect(url_for('login'))
    
    if session.get('role') not in ['doctor', 'nurse']:
        flash("Only doctors and nurses can analyze documents", "error")
        return redirect(url_for('doctor_dashboard'))
    
    if request.method == 'GET':
        # Show your existing analyze.html template
        return render_template('analyze.html')
    
    elif request.method == 'POST':
        # Check if a file was uploaded
        if 'file' not in request.files:
            flash("No file selected", "error")
            return redirect(url_for('analyze_document'))
        
        file = request.files['file']
        
        if file.filename == '':
            flash("No file selected", "error")
            return redirect(url_for('analyze_document'))
        
        # Get file extension
        file_ext = os.path.splitext(file.filename)[1].lower()
        
        # Only accept text files for now (simpler)
        if file_ext not in ['.txt']:
            flash("Please upload .txt files for analysis", "error")
            return redirect(url_for('analyze_document'))
        
        try:
            # Read the text file
            file_content = file.read().decode('utf-8', errors='ignore')
            
            if not file_content.strip():
                flash("File is empty", "error")
                return redirect(url_for('analyze_document'))
            
            # CHECK: Is NLP available?
            if nlp is None:
                flash("NLP analysis module not loaded. Check models/simple_nlp.py", "error")
                return redirect(url_for('analyze_document'))
            
            print(f"DEBUG: Analyzing file with NLP, content length: {len(file_content)}")
            
            # USE THE NLP CLASS TO ANALYZE
            results = nlp.analyze_text(file_content)
            
            print(f"DEBUG: NLP results: {results}")
            
            # Add file info to results
            results['filename'] = file.filename
            results['file_type'] = file_ext
            results['original_text_preview'] = file_content[:500] + "..." if len(file_content) > 500 else file_content
            
            # Save the analyzed file automatically
            from datetime import datetime
            save_filename = f"analyzed_{os.path.splitext(file.filename)[0]}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.txt"
            
            # Save to database
            conn = get_db()
            conn.execute(
                "INSERT INTO uploads (filename, uploader, role) VALUES (?, ?, ?)",
                (save_filename, session['username'], session['role'])
            )
            conn.commit()
            conn.close()
            
            # Save file to appropriate folder
            role_folder = 'doctors' if session['role'] == 'doctor' else 'nurses'
            folder_path = os.path.join(app.config['UPLOAD_FOLDER'], role_folder)
            filepath = os.path.join(folder_path, save_filename)
            
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(f"=== ORIGINAL FILE: {file.filename} ===\n\n")
                f.write(file_content + "\n\n")
                f.write(f"=== NLP ANALYSIS RESULTS ===\n\n")
                f.write(f"SUMMARY:\n{results['summary']}\n\n")
                f.write(f"MEDICAL TERMS FOUND ({len(results['medical_terms'])}):\n")
                for term in results['medical_terms']:
                    f.write(f"- {term}\n")
                f.write(f"\nTOP KEYWORDS:\n")
                for keyword in results['keywords']:
                    f.write(f"- {keyword}\n")
                if results.get('potential_diagnoses'):
                    f.write(f"\nPOTENTIAL DIAGNOSES:\n")
                    for diagnosis in results['potential_diagnoses']:
                        f.write(f"- {diagnosis}\n")
                if results.get('patient_info'):
                    f.write(f"\nPATIENT INFORMATION:\n")
                    for key, value in results['patient_info'].items():
                        f.write(f"- {key}: {value}\n")
            
            results['saved_as'] = save_filename
            flash(f"✅ Analysis complete! File saved as '{save_filename}'", "success")
            
            # Show results using your existing analysis_result.html template
            return render_template('analysis_result.html', results=results)
            
        except Exception as e:
            print(f"ERROR in analyze_document: {e}")
            import traceback
            traceback.print_exc()
            flash(f"Error analyzing file: {str(e)}", "error")
            return redirect(url_for('analyze_document'))

# ========== SIMPLE TEXT ANALYSIS ROUTE ==========
@app.route('/analyze_text', methods=['GET', 'POST'])
def analyze_text():
    """Direct text paste analysis"""
    if 'username' not in session:
        return redirect(url_for('login'))
    
    if request.method == 'GET':
        # Create a simple form for text input
        return '''
        <!DOCTYPE html>
        <html>
        <head><title>Analyze Text</title></head>
        <body style="font-family: Arial; padding: 20px;">
            <h2>Paste Clinical Text for Analysis</h2>
            <form method="POST">
                <textarea name="note_text" rows="10" cols="80" placeholder="Paste clinical note here..."></textarea><br><br>
                <button type="submit">Analyze</button>
                <a href="/doctor_dashboard">Cancel</a>
            </form>
        </body>
        </html>
        '''
    
    elif request.method == 'POST':
        note_text = request.form.get('note_text', '')
        
        if not note_text.strip():
            flash("Please enter some text to analyze", "error")
            return redirect(url_for('analyze_text'))
        
        if nlp is None:
            flash("NLP analysis is not available", "error")
            return redirect(url_for('doctor_dashboard'))
        
        results = nlp.analyze_text(note_text)
        results['filename'] = "pasted_text.txt"
        results['file_type'] = '.txt'
        results['text_preview'] = note_text[:500] + "..." if len(note_text) > 500 else note_text
        
        return render_template('analysis_result.html', results=results)

@app.route('/test_upload', methods=['GET', 'POST'])
def test_upload():
    """Simple test route for upload functionality"""
    if request.method == 'POST':
        if 'file' in request.files:
            file = request.files['file']
            if file.filename != '':
                filename = secure_filename(file.filename)
                return f"File received: {filename}, Size: {len(file.read())} bytes"
    
    return '''
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="file">
        <button type="submit">Test Upload</button>
    </form>
    '''

@app.route('/test_nlp')
def test_nlp():
    """Test if NLP is working"""
    test_text = """
    Patient John Smith, a 45-year-old male, presented with hypertension and headache.
    Blood pressure was 150/95 mmHg. Heart rate 85 bpm. Temperature 37.2°C.
    He complains of fatigue and shortness of breath. Diagnosed with essential hypertension.
    Prescribed Amlodipine 5mg daily and advised to reduce salt intake.
    Follow up in 2 weeks.
    """
    
    if nlp is None:
        return "NLP module not loaded. Check models/simple_nlp.py"
    
    results = nlp.analyze_text(test_text)
    
    html = f"""
    <h2>NLP Test Results</h2>
    <p><strong>Status:</strong> NLP module loaded successfully</p>
    <p><strong>Test Text:</strong> {test_text[:100]}...</p>
    <h3>Analysis Results:</h3>
    <pre>{results}</pre>
    <a href="/analyze_document">Go to Analyze Page</a>
    """
    
    return html

# ========== RUN APPLICATION ==========
if __name__ == '__main__':
    # Auto-create required files if missing
    if not os.path.exists('requirements.txt'):
        with open('requirements.txt', 'w') as f:
            f.write("Flask==2.3.3\nWerkzeug==2.3.7\n")
        print("Created requirements.txt")
    
    if not os.path.exists('models'):
        os.makedirs('models')
        print("Created models folder")
    
    if not os.path.exists('models/__init__.py'):
        with open('models/__init__.py', 'w') as f:
            pass
        print("Created models/__init__.py")
    
    app.run(debug=True, port=5000, host='0.0.0.0')