import React from 'react';
import ReactDOM from 'react-dom';

function App() {
  return (
      <div className="App">
        <h1>Hello</h1>
        <h2>React is working!</h2>
      </div>
  );
}

ReactDOM.render(<App />, document.querySelector('#root'));
