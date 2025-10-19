from flask import Flask, render_template, request, redirect, url_for, session, jsonify
import sqlite3
import os
from werkzeug.utils import secure_filename
from flask import send_from_directory
from flask import flash


app = Flask(__name__)
app.secret_key = "ggg"

# Database connection
def get_db():
    conn = sqlite3.connect("database.db")
    conn.row_factory = sqlite3.Row
    return conn

# Create table if not exists
with get_db() as conn:
    conn.execute('''CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT UNIQUE NOT NULL,
                    password TEXT NOT NULL,
                    role TEXT NOT NULL)''')
    

    # Create uploads table if not exists
with get_db() as conn:
    conn.execute('''CREATE TABLE IF NOT EXISTS uploads (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        filename TEXT NOT NULL,
                        uploader TEXT NOT NULL,
                        role TEXT NOT NULL,
                        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )''')


@app.route('/')
def home():
    return (render_template('homepage.html'))

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']

        conn = get_db()
        user = conn.execute("SELECT * FROM users WHERE username=? AND password=?", 
                            (username, password)).fetchone()
        conn.close()

        if user:
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
        username = request.form['username']
        password = request.form['password']
        role = request.form['role']

        try:
            with get_db() as conn:
                conn.execute("INSERT INTO users (username, password, role) VALUES (?, ?, ?)",
                             (username, password, role))
            return redirect(url_for('login'))
        except sqlite3.IntegrityError:
            return render_template('signup.html', error="Username already exists!")

    return render_template('signup.html')

#@app.route('/doctor_dashboard')
#def user_dashboard():
   # if session.get('role') in ['doctor', 'nurse']:
     #   return render_template('doctor_dashboard.html', username=session['username'])
    #return redirect(url_for('login'))







##Doctor and Nurse Dashboard with edit permissions for Doctor only##

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


# Create subfolders for doctors and nurses
os.makedirs(os.path.join(UPLOAD_FOLDER, 'doctors'), exist_ok=True)
os.makedirs(os.path.join(UPLOAD_FOLDER, 'nurses'), exist_ok=True)
@app.route('/upload', methods=['GET', 'POST'])
def upload():
    if session.get('role') not in ['doctor', 'nurse']:
        return redirect(url_for('login'))

    if request.method == 'POST':
        # If file is uploaded
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
            return "File uploaded successfully ✅"

    return render_template('upload.html')




@app.route('/view_uploads')
def view_uploads():
    role = session.get('role')
    username = session.get('username')

    if role not in ['doctor', 'nurse']:
        return redirect(url_for('login'))

    with get_db() as conn:
        if role == 'doctor':
            # Doctor sees all files (their own + nurses)
            files = conn.execute(
                "SELECT * FROM uploads ORDER BY uploaded_at DESC"
            ).fetchall()
        else:
            # Nurse sees only their own uploads
            files = conn.execute(
                "SELECT * FROM uploads WHERE uploader=? AND role=? ORDER BY uploaded_at DESC",
                (username, role)
            ).fetchall()

    return render_template('view_uploads.html', files=files, role=role)



@app.route('/download/<filename>/<uploader_role>')
def download_file(filename, uploader_role):
    role = session.get('role')
    if role not in ['doctor', 'nurse']:
        return redirect(url_for('login'))

    # Map role to folder name (plural)
    folder_name = 'doctors' if uploader_role == 'doctor' else 'nurses'
    folder_path = os.path.join(app.config['UPLOAD_FOLDER'], folder_name)

    # Nurses can only download their own files
    if role == 'nurse' and uploader_role != 'nurse':
        return "Unauthorized", 403

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
        return redirect(url_for('view_uploads'))

    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    return render_template('edit_file.html', filename=filename, content=content)







@app.route('/delete/<filename>/<uploader_role>', methods=['POST'])
def delete_file(filename, uploader_role):
    if session.get('role') != 'doctor':
        return redirect(url_for('login'))

    # Map role to actual folder name
    folder_name = 'doctors' if uploader_role == 'doctor' else 'nurses'
    folder_path = os.path.join(app.config['UPLOAD_FOLDER'], folder_name)
    filepath = os.path.join(folder_path, filename)

    # Debug print
    print("[DELETE] Trying to remove:", filepath)

    try:
        # Detailed debug info
        print("DELETE DEBUG:")
        print("Filename:", filename)
        print("Uploader role:", uploader_role)
        print("Folder path:", folder_path)
        print("Full path:", filepath)
        print("Exists on disk?", os.path.exists(filepath))

        if os.path.exists(filepath):
            os.remove(filepath)
            print("[DELETE] File removed from disk.")
        else:
            print("[DELETE] File does not exist at expected path.")

    except Exception as e:
        print("[DELETE] Error while deleting file:", e)

    # Remove from DB
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
            with get_db() as conn:
                conn.execute(
                    "UPDATE users SET password=? WHERE username=?",
                    (new_password, session['username'])
                )
            flash('Password updated successfully ✅', 'success')
            return redirect(url_for('settings'))

    return render_template('settings.html', username=session['username'])




@app.route('/logout')
def logout():
    session.clear()
    return redirect(url_for('login'))







#####



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


#admin add user route
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


#admin edit user route

    
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

#admin delete user route
@app.route('/admin/delete_user/<int:user_id>', methods=['POST'])
def delete_user(user_id):
    if session.get('role') != 'admin':
        return jsonify({'error': 'Unauthorized'})

    conn = get_db()
    conn.execute("DELETE FROM users WHERE id=?", (user_id,))
    conn.commit()
    conn.close()
    return jsonify({'message': 'User deleted successfully'})






######







if __name__ == '__main__':
    app.run(debug=True)

