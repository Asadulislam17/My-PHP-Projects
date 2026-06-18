import React, { useState, useEffect } from 'react';
import axios from 'axios'; 
import Header from '../components/Header';
import Nav from '../components/Nav';
import Footer from '../components/Footer';

export default function UserList() {
  const [users, setUsers] = useState([]);

  const apiUrl = 'http://localhost/My-PHP-Projects/reactproject2/api/view_users.php'; 

  useEffect(() => {
    axios.get(apiUrl)
      .then(res => {
        setUsers(res.data);
      })
      .catch(err => console.error(err));
  }, []);

  return (
    <>
      <Header />
      <Nav />
      
      <div className="container my-5">
        <div className="text-center mb-5">
          <h1 className="text-primary fw-bold display-4">User List</h1>
          <p className="fs-5 text-muted mt-3">
            আমরা বিশ্বাস করি প্রযুক্তির সঠিক ব্যবহারে জীবন আরও সহজ হয়।
          </p>
        </div>

        <div className="card shadow">
          <div className="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 className="mb-0">নিবন্ধিত ব্যবহারকারীদের তালিকা</h5>
            <span className="badge bg-primary">মোট: {users.length}</span>
          </div>
          <div className="card-body">
            
            <div className="table-responsive">
              <table className="table table-striped table-hover align-middle">
                <thead className="table-dark">
                  <tr>
                    <th>ID</th>
                    <th>ইউজারনেম</th>
                    <th>ইমেইল</th>
                    <th>শহর</th>
                    <th>জেন্ডার</th>
                    <th>ঠিকানা</th>
                    <th>Agree</th>
                  </tr>
                </thead>
                <tbody>
                  {users.length === 0 ? (
                    <tr>
                      <td colSpan="7" className="text-center text-muted py-4">
                        কোনো ডাটা পাওয়া যায়নি!
                      </td>
                    </tr>
                  ) : (
                    users.map((user) => (
                      <tr key={user.id}>
                        <td>{user.id}</td>
                        <td>{user.username}</td>
                        <td>{user.email}</td>
                        <td>{user.city}</td>
                        <td>{user.gender}</td>
                        <td>{user.address}</td>
                        <td>
                          <span className={`badge ${user.agree == 1 ? 'bg-success' : 'bg-danger'}`}>
                            {user.agree == 1 ? 'হ্যাঁ' : 'না'}
                          </span>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>

      <Footer />
    </>
  );
}
